<?php

namespace App\Entity;

use App\Enum\StatType;
use App\Repository\FightRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Context;
use JMS\Serializer\SerializationContext;

#[Entity(repositoryClass: FightRepository::class)]
class Fight
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    #[Exclude]
    private ?int $id = null;

    #[Groups(['fight'])]
    #[ManyToOne(targetEntity: UserCharacter::class, inversedBy: 'fights')]
    #[JoinColumn(name: 'character_id', referencedColumnName: 'id')]
    private UserCharacter $character;

    #[Groups(['fight'])]
    #[ManyToOne(targetEntity: UserCharacter::class)]
    #[JoinColumn(name: 'opponent_id', referencedColumnName: 'id')]
    private UserCharacter $opponent;

    #[Groups(['fight'])]
    #[OneToMany(mappedBy: 'fight', targetEntity: Action::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $actions;

    #[Groups(['fight'])]
    #[OneToOne(mappedBy: 'fight', targetEntity: Reward::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Reward $reward;

    public function __construct()
    {
        $this->actions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacter(): UserCharacter
    {
        return $this->character;
    }

    public function setCharacter(UserCharacter $character): void
    {
        $this->character = $character;
    }

    public function getOpponent(): UserCharacter
    {
        return $this->opponent;
    }

    public function setOpponent(UserCharacter $opponent): void
    {
        $this->opponent = $opponent;
    }

    public function getActions(): ArrayCollection|Collection
    {
        return $this->actions;
    }

    public function addAction(Action $action): self
    {
        if (!$this->actions->contains($action)) {
            $this->actions->add($action);
            $action->setFight($this);
        }
        return $this;
    }

    public function addActions(ArrayCollection $actions): void
    {
        foreach ($actions as $action)
        {
            $this->addAction($action);
        }
    }

    public function getReward(): Reward
    {
        return $this->reward;
    }

    public function setReward(Reward $reward): void
    {
        $this->reward = $reward;
    }

    public function generate(): void
    {
        $characterStats = $this->character->getFullStats();
        $opponentStats = $this->opponent->getFullStats();

        $characterHealth = $characterStats->get(StatType::HEALTH->value);
        $opponentHealth = $opponentStats->get(StatType::HEALTH->value);

        $playerTurn = rand($characterStats->get(StatType::SPEED->value), 100) > rand($opponentStats->get(StatType::SPEED->value), 100);

        //dump("character max health : " . $characterHealth);
        //dump("opponent max health : " . $opponentHealth);

        while($characterHealth > 0 && $opponentHealth > 0)
        {
            $action = new Action();

            if($playerTurn)
            {
                if(rand(0, 100) > $opponentStats->get(StatType::SPEED->value)) {
                    $action->setPlayerTeam(true);
                    $playerTurn = false;
                } else {
                    $action->setPlayerTeam(false);
                    $playerTurn = true;
                }
            } else {
                if(rand(0, 100) > $characterStats->get(StatType::SPEED->value)) {
                    $action->setPlayerTeam(false);
                    $playerTurn = true;
                } else {
                    $action->setPlayerTeam(true);
                    $playerTurn = false;
                }
            }

            $damage = 0;
            if (rand(0, 100) < ($action->isPlayerTeam() ? $characterStats->get(StatType::DODGE->value) : $opponentStats->get(StatType::DODGE->value))) {
                $action->setDodged(true);
            } else if (rand(0, 100) < ($action->isPlayerTeam() ? $characterStats->get(StatType::CRITICAL->value) : $opponentStats->get(StatType::CRITICAL->value))) {
                $action->setCriticalHit(true);
                $damage = ($action->isPlayerTeam() ? $characterStats->get(StatType::DAMAGE->value) : $opponentStats->get(StatType::DAMAGE->value)) * 2;
            } else {
                $damage = $action->isPlayerTeam() ? $characterStats->get(StatType::DAMAGE->value) : $opponentStats->get(StatType::DAMAGE->value);
            }

            if(!$action->isDodged()) {
                if ($action->isPlayerTeam()) {
                    $opponentArmor = $opponentStats->get(StatType::ARMOR->value);
                    $damage -= ($damage - $opponentArmor) > 0 ? $opponentArmor : $damage;
                    $opponentHealth -= $damage;
                    //dump("opponent takes " . $damage . " current life : " . $opponentHealth);
                } else {
                    $characterArmor = $characterStats->get(StatType::ARMOR->value);
                    $damage -= ($damage - $characterArmor) > 0 ? $characterArmor : $damage;
                    $characterHealth -= $damage;
                    //dump("player takes " . $damage . " current life : " . $characterHealth);
                }
            }

            $action->setDamage($damage);
            $this->addAction($action);

            if ($characterHealth <= 0) {
                break;
            }

            if ($opponentHealth <= 0) {
                break;
            }
        }

        $reward = new Reward();
        $this->setReward($reward);
        $reward->generate($this, ($characterHealth > 0));
    }

    public static function getSerializationContext(): Context|SerializationContext
    {
        return SerializationContext::create()->setGroups(array(
            'fight', // Serialize actions
            'character' => [
                'fighter',
                'body' => ['fighter'],
                'wallet' => [
                    'fighter',
                    'currencies' => ['fighter']
                ],
                'stats' => ['fighter'],
                'gear' => [
                    'fighter',
                    'equipments' => [
                        'fighter',
                        'item' => [
                            'fighter',
                            'stats' => ['fighter']
                        ],
                        'modifiers' => ['fighter']
                    ]
                ]
            ],

            'opponent' => [
                'opponent_fighter',
                'body' => ['opponent_fighter'],
                'stats' => ['opponent_fighter'],
                'gear' => [
                    'opponent_fighter',
                    'equipments' => [
                        'opponent_fighter',
                        'item' => [
                            'opponent_fighter',
                            'stats' => ['opponent_fighter']
                        ],
                        'modifiers' => ['opponent_fighter']
                    ]
                ]
            ]
        ));
    }
}