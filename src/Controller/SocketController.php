<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BaseItem;
use App\Entity\CharacterEquipment;
use App\Entity\Fight;
use App\Entity\Message;
use App\Entity\SocketMessage;
use App\Entity\TempFight;
use App\Entity\UserCharacter;
use App\Enum\SocketAction;
use App\Enum\SocketChannel;
use App\Exception\CharacterEquipmentException;
use App\Exception\FightException;
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

        $this->subscribeToChannel($conn, SocketChannel::DEFAULT->value, $username);
    }

    /**
     * @throws FightException
     * @throws CharacterEquipmentException
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
     */
    private function handleSocketChannel(ConnectionInterface $from, SocketMessage $socketMessage): bool
    {
        $action = $socketMessage->getAction() ?? 'unknown';
        $channel = $socketMessage->getChannel() ?? SocketChannel::DEFAULT->value;
        $username = $socketMessage->getUsername() ?? $this->botName;
        $content = $socketMessage->getContent() ?? '';

        switch ($channel) {
            case SocketChannel::DEFAULT->value:
                switch ($action) {
                    case SocketAction::SUBSCRIBE->value:
                        $this->subscribeToChannel($from, $content, $username);
                        return true;
                    case SocketAction::UNSUBSCRIBE->value:
                        $this->unsubscribeFromChannel($from, $content, $username);
                        return true;
                    case SocketAction::EQUIP->value:
                        /** @var CharacterEquipment $characterEquipment */
                        $characterEquipment = $this->entityManager->getRepository(CharacterEquipment::class)->findById(intval($content));
                        return $this->equip($from, $username, $characterEquipment);
                    case SocketAction::UN_EQUIP->value:
                        /** @var CharacterEquipment $characterEquipment */
                        $characterEquipment = $this->entityManager->getRepository(CharacterEquipment::class)->findById(intval($content));
                        return $this->unEquip($from, $username, $characterEquipment);
                    case SocketAction::SHOP_LIST->value:
                        $items = $this->entityManager->getRepository(BaseItem::class)->findAll();
                        $from->send(json_encode([
                            'action' => SocketAction::SHOP_LIST,
                            'channel' => SocketChannel::DEFAULT,
                            'username' => $this->botName,
                            'content' => $this->serializer->serialize($items, 'json', SerializationContext::create()->setGroups(['shopList']))
                        ]));
                        return true;
                    case SocketAction::RANK_LIST->value:
                        $repository = $this->entityManager->getRepository(UserCharacter::class);
                        /** @var UserCharacter $character */
                        $character = $repository->findPlayerByUsername($username);
                        $characters = $repository->findPlayersByCharacterRank($character);
                        $from->send(json_encode([
                            'action' => SocketAction::RANK_LIST,
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
                    case SocketAction::MESSAGE->value:
                        return $this->sendMessageToChannel($from, $channel, $username, $content);
                    default:
                        echo sprintf('Action "%s" is not supported yet!', $action) . "\n";
                        break;
                }
                break;
            case SocketChannel::FIGHT_SUFFIX->value . $username:
                switch ($action) {
                    case SocketAction::ATTACK->value:
                        $this->attack($from, $username);
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
        unset($this->users[$conn->resourceId]);
        $this->connections->detach($conn);
    }

    public function handleError(ConnectionInterface $conn, Exception $e): void
    {
        $this->writeToConsole('new error : ' . $e->getMessage() . ' of type : ' . $e::class);
        switch ($e::class) {
            case FightException::class:
            case CharacterEquipmentException::class:
                $conn->send(json_encode([
                    'action' => SocketAction::ERROR,
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
     */
    private function subscribeToChannel(ConnectionInterface $conn, $channel, string $username): void
    {
        switch ($channel) {
            case SocketChannel::DEFAULT->value:
                break;
            case SocketChannel::CHAT_DEFAULT->value:
                $conn->send(json_encode([
                    'action' => SocketAction::MESSAGE_LIST,
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
                    $opponent = $this->entityManager->getRepository(Fight::class)->findOpponent($this->getCharacter($username));

                    $this->users[$conn->resourceId]['tempFight']
                        = new TempFight($this->getCharacter($username), $opponent);

                    $conn->send(json_encode([
                        'action' => SocketAction::FIGHT_START,
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
                case SocketChannel::CHAT_DEFAULT->value:
                case SocketChannel::DEFAULT->value:
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

    private function sendMessageToChannel(ConnectionInterface $conn, $channel, $user, $content): bool
    {
        if (!isset($this->users[$conn->resourceId]['channels'][$channel])) {
            return false;
        }

        if($user != $this->botName) {
            $message = new Message();
            $message->setCharacter($this->entityManager->getRepository(UserCharacter::class)->findPlayerByUsername($user));
            $message->setContent($content);

            $this->entityManager->persist($message);
            $this->entityManager->flush();
        }

        foreach ($this->users as $resourceId => $userConnection) {
            if (array_key_exists($channel, $userConnection['channels'])) {
                $userConnection['connection']->send(json_encode([
                    'action' => 'message',
                    'channel' => $channel,
                    'username' => $user,
                    'content' => $content
                ]));
            }
        }
        return true;
    }

    /**
     * @throws CharacterEquipmentException
     */
    private function equip(ConnectionInterface $conn, string $username, CharacterEquipment $characterEquipment): bool
    {
        /** @var UserCharacter $character */
        $character = $this->entityManager->getRepository(UserCharacter::class)->findPlayerByUsername($username);
        $character->equip($characterEquipment);

        $this->entityManager->persist($character);
        $this->entityManager->flush();

        $conn->send(json_encode([
            'action' => SocketAction::EQUIP,
            'channel' => SocketChannel::DEFAULT,
            'username' => $username,
            'content' => $characterEquipment->getId()
        ]));
        return true;
    }

    /**
     * @throws CharacterEquipmentException
     */
    private function unEquip(ConnectionInterface $conn, string $username, CharacterEquipment $characterEquipment): bool
    {
        /** @var UserCharacter $character */
        $character = $this->entityManager->getRepository(UserCharacter::class)->findPlayerByUsername($username);
        $character->unEquip($characterEquipment);

        $this->entityManager->persist($character);
        $this->entityManager->flush();

        $conn->send(json_encode([
            'action' => SocketAction::UN_EQUIP,
            'channel' => SocketChannel::DEFAULT,
            'username' => $username,
            'content' => $characterEquipment->getEquipmentSlot()->value
        ]));
        return true;
    }

    /**
     * @throws FightException
     */
    private function attack(ConnectionInterface $conn, string $username): void
    {
        if (!array_key_exists(SocketChannel::FIGHT_SUFFIX->value . $username, $this->users[$conn->resourceId]['channels'])
            || $this->users[$conn->resourceId]['tempFight'] == null) {
            throw new FightException('No current fight found');
        }

        $actions = $this->users[$conn->resourceId]['tempFight']->attack();
        $conn->send(json_encode([
            'action' => SocketAction::ATTACK,
            'channel' => SocketChannel::FIGHT_SUFFIX->value . $username,
            'username' => $this->botName,
            'content' => $this->serializer->serialize(
                $actions,
                'json',
                Fight::getSerializationContext()
            )
        ]));

        if($this->users[$conn->resourceId]['tempFight']->fightIsOver())
        {
            $this->entityManager->persist($this->users[$conn->resourceId]['tempFight']->getFight());
            $this->entityManager->flush();

            $conn->send(json_encode([
                'action' => SocketAction::FIGHT_OVER,
                'channel' => SocketChannel::FIGHT_SUFFIX->value . $username,
                'username' => $this->botName,
                'content' => $this->serializer->serialize(
                    $this->users[$conn->resourceId]['tempFight']->getReward(),
                    'json',
                    Fight::getSerializationContext()
                )
            ]));
            $this->unsubscribeFromChannel($conn, SocketChannel::FIGHT_SUFFIX->value . $username, $username);
        }
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