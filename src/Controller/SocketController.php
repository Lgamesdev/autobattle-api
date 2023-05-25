<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BaseCharacterItem;
use App\Entity\BaseItem;
use App\Entity\CharacterEquipment;
use App\Entity\Fight;
use App\Entity\Message;
use App\Entity\Reward;
use App\Entity\SocketMessage;
use App\Entity\TempFight;
use App\Entity\UserCharacter;
use App\Entity\Wallet;
use App\Enum\CurrencyType;
use App\Enum\FightActionType;
use App\Enum\InitialisationStage;
use App\Enum\SocketReceiveAction;
use App\Enum\SocketChannel;
use App\Enum\SocketSendAction;
use App\Enum\StatType;
use App\Exception\CharacterEquipmentException;
use App\Exception\FightException;
use App\Exception\ShopException;
use App\Exception\UserCharacterException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SocketController
{
    private string $botName = 'ChatBot';
    private array $users = [];

    protected SplObjectStorage $connections;
    private EventDispatcherInterface $dispatcher;
    private TokenStorageInterface $tokenStorage;
    private EntityManagerInterface $entityManager;
    private JWTTokenManagerInterface $tokenManager;
    private SerializerInterface $serializer;

    public function __construct(EventDispatcherInterface $dispatcher,
                                TokenStorageInterface $storage,
                                EntityManagerInterface $entityManager,
                                JWTTokenManagerInterface $tokenManager,
                                SerializerInterface $serializer)
    {
        $this->connections = new SplObjectStorage;
        $this->dispatcher = $dispatcher;
        $this->tokenStorage = $storage;
        $this->entityManager = $entityManager;
        $this->tokenManager = $tokenManager;
        $this->serializer = $serializer;
    }

    /**
     * @throws FightException
     */
    public function handleOpen(ConnectionInterface $conn): void
    {
        $username = $this->getUsername($conn);

        $this->writeToConsole('new connection from : ' . $username . ' #' . $conn->resourceId);

        $out = array_map(function ($ar) use ($conn) {
            if ($ar['username'] == $this->getUsername($conn)) {
                return $ar;
            }
        },
            $this->users
        );

        if($out) {
            $this->connections->detach($this->users[array_key_first($out)]['connection']);
            $this->users[array_key_first($out)]['connection']->close();
        }

        $this->users[$conn->resourceId] = [
            'connection' => $conn,
            'username' => $username,
            'channels' => []
        ];

        $this->connections->attach($conn);
    }

    /**
     * @throws FightException
     * @throws CharacterEquipmentException
     * @throws UserCharacterException
     * @throws ShopException
     */
    public function handleMessage(ConnectionInterface $from, string $msg): bool
    {
        $this->writeToConsole('new message : ' . $msg);

        $socketMessage = $this->serializer->deserialize($msg, SocketMessage::class, 'json');

        if ($socketMessage === null) {
            return false;
        }

        return $this->handleSocketChannel($from, $socketMessage);
    }

    /**
     * @throws FightException
     * @throws CharacterEquipmentException
     * @throws UserCharacterException
     * @throws ShopException
     */
    private function handleSocketChannel(ConnectionInterface $from, SocketMessage $socketMessage): bool
    {
        $action = empty($socketMessage->getAction()) ? 'unknown' : $socketMessage->getAction();
        $channel = empty($socketMessage->getChannel()) ? SocketChannel::DEFAULT->value : $socketMessage->getChannel();
        $username = empty($socketMessage->getUsername()) ? $this->botName : $socketMessage->getUsername();
        $content = empty($socketMessage->getContent()) ? '' : $socketMessage->getContent();

        switch ($channel) {
            case SocketChannel::DEFAULT->value:
                switch ($action) {
                    case SocketReceiveAction::TRY_SUBSCRIBE->value:
                        $this->subscribeToChannel($from, $content, $username);
                        return true;

                    case SocketReceiveAction::TRY_UNSUBSCRIBE->value:
                        $this->unsubscribeFromChannel($from, $content, $username);
                        return true;

                    case SocketReceiveAction::TUTORIAL_FINISHED->value:
                        return $this->tutorialFinished($from, $username);

                    case SocketReceiveAction::TRY_EQUIP->value:
                        /** @var CharacterEquipment $characterEquipment */
                        $characterEquipment = $this->entityManager->getRepository(CharacterEquipment::class)->findById(intval($content));
                        return $this->equip($from, $username, $characterEquipment);

                    case SocketReceiveAction::TRY_UN_EQUIP->value:
                        /** @var CharacterEquipment $characterEquipment */
                        $characterEquipment = $this->entityManager->getRepository(CharacterEquipment::class)->findById(intval($content));
                        return $this->unEquip($from, $username, $characterEquipment);

                    case SocketReceiveAction::TRY_ADD_STAT_POINT->value:
                        $statLabel = json_decode($content);
                        $statType = StatType::from($statLabel);
                        return $this->addStatPoint($from, $username, $statType);

                    case SocketReceiveAction::GET_SHOP_LIST->value:
                        $items = $this->entityManager->getRepository(BaseItem::class)->findAll();
                        $from->send(json_encode([
                            'action' => SocketSendAction::SHOP_LIST,
                            'channel' => SocketChannel::DEFAULT,
                            'username' => $this->botName,
                            'content' => $this->serializer->serialize($items, 'json', SerializationContext::create()->setGroups(['shopList']))
                        ]));
                        return true;

                    case SocketReceiveAction::TRY_BUY_ITEM->value:
                        $item = $this->entityManager->getRepository(BaseItem::class)->findById(intval($content));
                        return $this->buyItem($from, $username, $item);

                    case SocketReceiveAction::TRY_SELL_ITEM->value:
                        $characterItem = $this->entityManager->getRepository(BaseCharacterItem::class)->findById(intval($content));
                        return $this->sellItem($from, $username, $characterItem);

                    case SocketReceiveAction::GET_RANK_LIST->value:
                        $repository = $this->entityManager->getRepository(UserCharacter::class);
                        /** @var UserCharacter $character */
                        $character = $this->getCharacter($username);
                        $characters = $repository->findPlayersByCharacterRank($character);
                        $from->send(json_encode([
                            'action' => SocketSendAction::RANK_LIST,
                            'channel' => SocketChannel::DEFAULT,
                            'username' => $this->botName,
                            'content' => $this->serializer->serialize($characters, 'json', SerializationContext::create()->setGroups(['opponent_fighter']))
                        ]));
                        return true;
                    default:
                        echo sprintf('Action "%s" is not supported yet!', $action) . "\n";
                        break;
                }
                break;
            case SocketChannel::CHAT_DEFAULT->value:
                switch ($action) {
                    case SocketReceiveAction::SEND_MESSAGE->value:
                        return $this->sendMessageToChannel($from, $channel, $username, $content);
                    default:
                        echo sprintf('Action "%s" is not supported yet!', $action) . "\n";
                        break;
                }
                break;
            case SocketChannel::FIGHT_SUFFIX->value . $username:
                switch ($action) {
                    case SocketReceiveAction::TRY_ATTACK->value:
                        $fightAction = FightActionType::from($content);
                        $this->attack($from, $username, $fightAction);
                        break;
                    default:
                        echo sprintf('Action "%s" is not supported yet!', $action) . "\n";
                        break;
                }
                break;
            default:
                echo sprintf('Channel "%s" is not supported yet!', $channel) . "\n";
                break;
        }
        return false;
    }

    public function handleClose(ConnectionInterface $conn): void
    {
        $this->writeToConsole('connection closed !');
        if($this->users[$conn->resourceId]['tempFight'] != null)
        {
            $fight = $this->users[$conn->resourceId]['tempFight']->finishFight();
            $this->entityManager->persist($fight);
            $this->entityManager->flush();

            echo 'finish fight and persist before closing for '
                . $this->users[$conn->resourceId]['username'] . ' : '
                . $this->serializer->serialize(
                    $fight,
                    'json',
                    Fight::getSerializationContext()
                );
        }

        unset($this->users[$conn->resourceId]);
        $this->connections->detach($conn);
    }

    public function handleError(ConnectionInterface $conn, Exception $e): void
    {
        $this->writeToConsole('new error : ' . $e->getMessage() . ' of type : ' . $e::class);
        switch ($e::class) {
            case FightException::class:
            case CharacterEquipmentException::class:
            case UserCharacterException::class:
            case ShopException::class:
                $conn->send(json_encode([
                    'action' => SocketSendAction::ERROR,
                    'channel' => SocketChannel::DEFAULT,
                    'type' => gettype($e),
                    'code' => $e->getCode(),
                    'content' => $e->getMessage()
                ]));
                break;
            default:
                /*$conn->send(new JsonResponse(
                    $this->serializer->serialize($e, 'json'),
                    Response::HTTP_FORBIDDEN,
                    [],
                    true)
                );*/
                $this->connections->detach($conn);
                $conn->close();
        }
    }

    /**
     * @throws FightException
     * @throws Exception
     */
    private function subscribeToChannel(ConnectionInterface $conn, string $channel, string $username): void
    {
        if (array_key_exists($channel, $this->users[$conn->resourceId]['channels'])) {
            throw new Exception('already subscribed to channel ' . $channel);
        }

        switch ($channel) {
            case SocketChannel::DEFAULT->value:
                //echo $username . "subscribed to default Channel \n";
                $character = $this->getCharacter($username);
                foreach (InitialisationStage::cases() as $stage) {
                    $conn->send(json_encode([
                        'action' => SocketSendAction::INITIALISATION,
                        'channel' => SocketChannel::DEFAULT,
                        'username' => $this->botName,
                        'content' => $this->serializer->serialize([
                                'stage' => $stage->value,
                                'value' => $this->getInitialisationResult($stage, $character)
                            ],
                            'json',
                        )
                    ]));
                }
                break;
            case SocketChannel::CHAT_DEFAULT->value:
                $conn->send(json_encode([
                    'action' => SocketSendAction::MESSAGE_LIST,
                    'channel' => SocketChannel::CHAT_DEFAULT,
                    'username' => $this->botName,
                    'content' => $this->serializer->serialize(
                        $this->entityManager->getRepository(Message::class)->findAll(),
                        'json',
                        SerializationContext::create()->setGroups(['message'])
                    )
                ]));
                break;
            case SocketChannel::FIGHT_SUFFIX->value . $username:
                if(!array_key_exists('tempFight', $this->users[$conn->resourceId])) {
                    //à voir si peut mieux faire ce check : combat possible sans passer par le main menu...
                    if (array_key_exists(SocketChannel::DEFAULT->value, $this->users[$conn->resourceId]['channels'])) {
                        $this->unsubscribeFromChannel($conn, SocketChannel::DEFAULT->value, $username);
                    }
                    $opponent = $this->entityManager->getRepository(Fight::class)->findOpponent($this->getCharacter($username));

                    $this->users[$conn->resourceId]['tempFight']
                        = new TempFight($this->getCharacter($username), $opponent);

                    $conn->send(json_encode([
                        'action' => SocketSendAction::FIGHT_START,
                        'channel' => SocketChannel::FIGHT_SUFFIX->value . $username,
                        'username' => $this->botName,
                        'content' => $this->serializer->serialize(
                            $this->users[$conn->resourceId]['tempFight']->getFight(),
                            'json',
                            Fight::getSerializationContext()
                        )
                    ]));

                    //$this->attack($conn, $username);
                } else {
                    throw new FightException('A fight is already launched');
                }
                break;
            default:
                echo sprintf('Channel "%s" is not supported yet!', $channel) . "\n";
                return;
        }

        $this->users[$conn->resourceId]['channels'][$channel] = $channel;
        $this->sendMessageToChannel(
            $conn,
            $channel,
            $this->botName,
            $username . ' joined #' . $channel
        );
    }

    /**
     * @throws FightException
     * @throws Exception
     */
    private function unsubscribeFromChannel(ConnectionInterface $conn, string $channel, string $username): void
    {
        if (array_key_exists($channel, $this->users[$conn->resourceId]['channels'])) {
            switch ($channel) {
                case SocketChannel::DEFAULT->value:
                case SocketChannel::CHAT_DEFAULT->value:
                    break;
                case SocketChannel::FIGHT_SUFFIX->value . $username:
                    if($this->users[$conn->resourceId]['tempFight'] != null) {
                        unset($this->users[$conn->resourceId]['tempFight']);
                    } else {
                        throw new Exception('TempFight not found on unsubscribe');
                    }
                    break;
                default:
                    echo sprintf('Channel "%s" unsubscription is not supported yet!', $channel) . "\n";
                    return;
            }

            unset($this->users[$conn->resourceId]['channels'][$channel]);
            $this->sendMessageToChannel(
                $conn,
                $channel,
                $this->botName,
                $username . ' left #' . $channel
            );
        } else {
            throw new Exception('channel not found');
        }
    }

    public function getInitialisationResult(InitialisationStage $stage, UserCharacter $character)
    {
        switch ($stage)
        {
            case InitialisationStage::BODY:
                return $this->serializer->serialize(
                    $character->getBody(),
                    'json',
                    SerializationContext::create()->setGroups(['body'])
                );
            case InitialisationStage::PROGRESSION:
                return $this->serializer->serialize(
                    [
                        'level' => $character->getLevel(),
                        'experience' => $character->getExperience(),
                        'maxExperience' => $character->calculateRequiredExperienceForLevel(),
                        'statPoint' => $character->getStatPoints(),
                        'ranking' => $character->getRanking(),
                    ],
                    'json'
                );
            case InitialisationStage::WALLET:
                return $this->serializer->serialize(
                    $this->entityManager->getRepository(Wallet::class)->findCharacterWallet($character),
                    'json',
                    SerializationContext::create()->setGroups(['characterWallet'])
                );
            case InitialisationStage::EQUIPMENT:
                return $this->serializer->serialize(
                    $character->getGear(),
                    'json',
                    SerializationContext::create()->setGroups(['gear'])
                );
            case InitialisationStage::INVENTORY:
                return $this->serializer->serialize(
                    $character->getInventory(),
                    'json',
                    SerializationContext::create()->setGroups(['playerInventory'])
                );
            case InitialisationStage::CHARACTER_STATS:
                return $this->serializer->serialize(
                    $character->getStats(),
                    'json',
                    SerializationContext::create()->setGroups(['characterStat'])
                );
        }
    }

    private function tutorialFinished(ConnectionInterface $conn, string $username): bool
    {
        /** @var UserCharacter $user */
        $user = $this->getCharacter($username);
        $user->setTutorialDone(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $conn->send(json_encode([
            'action' => SocketSendAction::TUTORIAL_DONE,
            'channel' => SocketChannel::DEFAULT,
            'username' => $username
        ]));
        return true;
    }

    private function sendMessageToChannel(ConnectionInterface $conn, string $channel, string $username, string $content): bool
    {
        if (!isset($this->users[$conn->resourceId]['channels'][$channel])) {
            return false;
        }

        if($username != $this->botName) {
            $message = new Message();
            $message->setCharacter($this->getCharacter($username));
            $message->setContent($content);

            $this->entityManager->persist($message);
            $this->entityManager->flush();
        }

        foreach ($this->users as $resourceId => $userConnection) {
            if (array_key_exists($channel, $userConnection['channels'])) {
                $userConnection['connection']->send(json_encode([
                    'action' => SocketSendAction::MESSAGE,
                    'channel' => $channel,
                    'username' => $username,
                    'content' => $content
                ]));
            }
        }
        return true;
    }

    /**
     * @throws UserCharacterException
     */
    private function addStatPoint(ConnectionInterface $conn, string $username, StatType $statType): bool
    {
        /** @var UserCharacter $character */
        $character = $this->getCharacter($username);
        $stat = $character->addStatPoint($statType);

        $this->entityManager->persist($character);
        $this->entityManager->flush();

        $conn->send(json_encode([
            'action' => SocketSendAction::ADD_STAT_POINT,
            'channel' => SocketChannel::DEFAULT,
            'username' => $username,
            'content' => $this->serializer->serialize(
                $stat,
                'json',
                SerializationContext::create()->setGroups(['characterStat'])
            )
        ]));
        return true;
    }

    /**
     * @throws CharacterEquipmentException
     */
    private function equip(ConnectionInterface $conn, string $username, CharacterEquipment $characterEquipment): bool
    {
        /** @var UserCharacter $character */
        $character = $this->getCharacter($username);
        $character->equip($characterEquipment);

        $this->entityManager->persist($character);
        $this->entityManager->flush();

        $conn->send(json_encode([
            'action' => SocketSendAction::EQUIP,
            'channel' => SocketChannel::DEFAULT,
            'username' => $username,
            'content' => $characterEquipment->getId()
        ]));
        return true;
    }

    private function unEquip(ConnectionInterface $conn, string $username, CharacterEquipment $characterEquipment): bool
    {
        /** @var UserCharacter $character */
        $character = $this->getCharacter($username);
        $character->unEquip($characterEquipment);

        $this->entityManager->persist($character);
        $this->entityManager->flush();

        $conn->send(json_encode([
            'action' => SocketSendAction::UN_EQUIP,
            'channel' => SocketChannel::DEFAULT,
            'username' => $username,
            'content' => $characterEquipment->getEquipmentSlot()->value
        ]));
        return true;
    }

    /**
     * @throws ShopException
     */
    private function buyItem(ConnectionInterface $conn, string $username, BaseItem $item): bool
    {
        /** @var UserCharacter $character */
        $character = $this->getCharacter($username);

        $characterItem = $character->tryBuy($item);

        if($characterItem == null) {
            throw new ShopException('An error occurred during item buy');
        }

        $this->entityManager->persist($character);
        $this->entityManager->flush();

        $conn->send(json_encode([
            'action' => SocketSendAction::BUY_ITEM,
            'channel' => SocketChannel::DEFAULT,
            'username' => $username,
            'content' => $this->serializer->serialize($characterItem, 'json', SerializationContext::create()->setGroups(['playerInventory']))
        ]));

        return true;
    }

    /**
     * @throws ShopException
     */
    private function sellItem(ConnectionInterface $conn, string $username, BaseCharacterItem $characterItem): bool
    {
        $itemId = $characterItem->getId();

        /** @var UserCharacter $character */
        $character = $this->getCharacter($username);

        $isSold = $character->sell($characterItem);

        if(!$isSold) {
            throw new ShopException('An error has occurred when selling item');
        }

        $this->entityManager->persist($character);
        $this->entityManager->flush();

        $conn->send(json_encode([
            'action' => SocketSendAction::SELL_ITEM,
            'channel' => SocketChannel::DEFAULT,
            'username' => $username,
            'content' => $itemId
        ]));

        return true;
    }

    /**
     * @throws FightException
     */
    private function attack(ConnectionInterface $conn, string $username, FightActionType $actionType): void
    {
        if (!array_key_exists(SocketChannel::FIGHT_SUFFIX->value . $username, $this->users[$conn->resourceId]['channels'])
            || $this->users[$conn->resourceId]['tempFight'] == null) {
            throw new FightException('No current fight found');
        }

        $actions = $this->users[$conn->resourceId]['tempFight']->attack($actionType);
        $conn->send(json_encode([
            'action' => SocketSendAction::ATTACK,
            'channel' => SocketChannel::FIGHT_SUFFIX->value . $username,
            'username' => $username,
            'content' => $this->serializer->serialize(
                $actions,
                'json',
                Fight::getSerializationContext()
            )
        ]));

        if($this->users[$conn->resourceId]['tempFight']->fightIsOver())
        {
            $callbackMessages = $this->handleReward($this->users[$conn->resourceId]['tempFight']->getReward());

            $this->entityManager->persist($this->users[$conn->resourceId]['tempFight']->getFight());
            $this->entityManager->flush();

            /*echo $username . 'fight\'s over, result : ' . $this->serializer->serialize(
                $this->users[$conn->resourceId]['tempFight']->getReward(),
                'json',
                Fight::getSerializationContext()
            ) . "\n";*/

            $conn->send(json_encode([
                'action' => SocketSendAction::FIGHT_OVER,
                'channel' => SocketChannel::FIGHT_SUFFIX->value . $username,
                'username' => $this->botName,
                'content' => $this->serializer->serialize(
                    $this->users[$conn->resourceId]['tempFight']->getReward(),
                    'json',
                    Fight::getSerializationContext()
                )
            ]));

            foreach ($callbackMessages as $message)
            {
                $conn->send($message);
            }
            $this->unsubscribeFromChannel($conn, SocketChannel::FIGHT_SUFFIX->value . $username, $username);
        }
    }

    private function handleReward(Reward $reward): ArrayCollection
    {
        $callbackMessages = new ArrayCollection();

        $character = $reward->getFight()->getCharacter();

        echo $character->getUsername() . ' (lvl. ' . $character->getLevel() . ') gained xp ' . $reward->getExperience()
            . ' (actual xp : ' . $character->getExperience() . ' / ' . $character->calculateRequiredExperienceForLevel() . ") \n";

        $experience = $character->getExperience() + $reward->getExperience();
        while(!$character->isMaxLevel() && $experience >= $character->calculateRequiredExperienceForLevel())
        {
            $experience -= $character->calculateRequiredExperienceForLevel();
            echo $character->getUsername() . " level up ! (level " . $character->getLevel() . ") \n";
            $callbackMessages->add(json_encode([
                'action' => SocketSendAction::EXPERIENCE_GAINED,
                'channel' => SocketChannel::FIGHT_SUFFIX->value . $character->getUsername(),
                'username' => $this->botName,
                'content' => $this->serializer->serialize([
                    'level' => $character->getLevel(),
                    'oldExperience' => $character->getExperience(),
                    'aimedExperience' => $character->calculateRequiredExperienceForLevel(),
                    'maxExperience' => $character->calculateRequiredExperienceForLevel()
                ],
                'json',
                )
            ]));
            $character->levelUp();
        }
        $callbackMessages->add(json_encode([
            'action' => SocketSendAction::EXPERIENCE_GAINED,
            'channel' => SocketChannel::FIGHT_SUFFIX->value . $character->getUsername(),
            'username' => $this->botName,
            'content' => $this->serializer->serialize([
                'level' => $character->getLevel(),
                'oldExperience' => $character->getExperience(),
                'aimedExperience' => $experience,
                'maxExperience' => $character->calculateRequiredExperienceForLevel()
            ],
                'json',
            )
        ]));
        $character->setExperience($experience);
        echo 'new xp for ' . $character->getUsername() . ' (lvl.' . $character->getLevel() . ') : '
            . $character->getExperience() . ' / ' . $character->calculateRequiredExperienceForLevel() . "\n";

        foreach ($reward->getCurrencies() as $currency) {
            $character->getWallet()->addCurrency($currency);
        }
        $character->addRanking($reward->getRanking());

        return $callbackMessages;
    }

    public function getUsername(ConnectionInterface $conn) : string
    {
        $headers = $conn->httpRequest->getHeaders();
        return $this->tokenManager->parse($headers['Authorization'][0])['username'];
    }

    public function getCharacter(string $username)
    {
        return $this->entityManager->getRepository(UserCharacter::class)->findPlayerByUsername($username);
    }

    private function writeToConsole(string $message): void
    {
        $this->dispatcher->dispatch(
            new GenericEvent($message),
            'console.writeline'
        );
    }
}