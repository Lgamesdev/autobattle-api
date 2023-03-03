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

class TempFight
{
    private int $actualPlayerLife;
    private int $actualOpponentLife;

    private UserCharacter $player;
    private UserCharacter $opponent;

    private ArrayCollection $playerStats;
    private ArrayCollection $opponentStats;

    private Fight $fight;

    private bool $isPlayerTurn;

    public function __construct(UserCharacter $player, UserCharacter $opponent)
    {
        $this->player = $player;
        $this->opponent = $opponent;

        $this->playerStats = $this->player->getFullStats();
        $this->opponentStats = $this->opponent->getFullStats();

        $this->actualPlayerLife = $this->playerStats->get(StatType::HEALTH->value);
        $this->actualOpponentLife = $this->opponentStats->get(StatType::HEALTH->value);

        $this->fight = new Fight();
        $this->fight->setCharacter($player);
        $this->fight->setOpponent($opponent);

        $this->isPlayerTurn = $this->playerStats->get(StatType::SPEED->value) >= $this->opponentStats->get(StatType::SPEED->value);

        if(!$this->isPlayerTurn)
        {
            $this->fight->addAction($this->createAction());
        }
    }

    public function attack(): ArrayCollection|Collection
    {
        $actions = new ArrayCollection();

        if(!$this->fight->getActions()->isEmpty()) {
            $this->isPlayerTurn = true;
        }

        if(!$this->fightIsOver())
        {
            //1rst attack action by player
            $actions->add($this->createAction());

            if (!$this->fightIsOver())
            {
                //2nd attack action
                if(!$this->isPlayerTurn) {
                    $this->isPlayerTurn = !(rand(0, 100) < $this->opponentStats->get(StatType::SPEED->value));
                } else {
                    $this->isPlayerTurn = rand(0, 100) < $this->playerStats->get(StatType::SPEED->value);
                }
                $actions->add($this->createAction());

                if (!$this->fightIsOver())
                {
                    //Check if player hitted 2 times (max) in a row, else opponent will try a second hit
                    if ($this->isPlayerTurn)
                    {
                        //3rd attack action by opponent (forced)
                        $this->isPlayerTurn = false;
                        $actions->add($this->createAction());

                        if (!$this->fightIsOver())
                        {
                            //4rth attack action by opponent if he roll speed
                            $this->isPlayerTurn = rand(0, 100) < $this->opponentStats->get(StatType::SPEED->value);

                            //Check if it's opponent turn or skip
                            if(!$this->isPlayerTurn) {
                                $actions->add($this->createAction());

                                if($this->fightIsOver()) {
                                    $this->fight->addActions($actions);
                                    return $actions;
                                }
                            }
                        } else {
                            $this->fight->addActions($actions);
                            return $actions;
                        }
                    } else {
                        //3rd attack action if opponent roll speed
                        $this->isPlayerTurn = !(rand(0, 100) < $this->opponentStats->get(StatType::SPEED->value));

                        //Check if it's opponent turn or skip
                        if(!$this->isPlayerTurn) {
                            $actions->add($this->createAction());

                            if($this->fightIsOver()) {
                                $this->fight->addActions($actions);
                                return $actions;
                            }
                        }

                        $this->fight->addActions($actions);
                        return $actions;
                    }
                } else {
                    $this->fight->addActions($actions);
                    return $actions;
                }
            } else {
                $this->fight->addActions($actions);
                return $actions;
            }
        }

        $this->fight->addActions($actions);
        return $actions;
    }

    public function finishFight(): Fight
    {
        while(!$this->fightIsOver())
        {
            $this->fight->addAction($this->createAction());
        }

        return $this->fight;
    }

    private function createAction(): Action
    {
        $action = new Action();
        $action->setPlayerTeam($this->isPlayerTurn);

        $damage = 0;
        if (rand(0, 100) < ($action->isPlayerTeam() ? $this->playerStats->get(StatType::DODGE->value) : $this->opponentStats->get(StatType::DODGE->value))) {
            $action->setDodged(true);
        } else if (rand(0, 100) < ($action->isPlayerTeam() ? $this->playerStats->get(StatType::CRITICAL->value) : $this->opponentStats->get(StatType::CRITICAL->value))) {
            $action->setCriticalHit(true);
            $damage = ($action->isPlayerTeam() ? $this->playerStats->get(StatType::DAMAGE->value) : $this->opponentStats->get(StatType::DAMAGE->value)) * 2;
        } else {
            $damage = $action->isPlayerTeam() ? $this->playerStats->get(StatType::DAMAGE->value) : $this->opponentStats->get(StatType::DAMAGE->value);
        }

        if(!$action->isDodged()) {
            if ($action->isPlayerTeam()) {
                $opponentArmor = $this->opponentStats->get(StatType::ARMOR->value);
                $damage -= ($damage - $opponentArmor) > 0 ? $opponentArmor : $damage;
                $this->actualOpponentLife -= $damage;
                //echo "opponent takes " . $damage . " current life : " . $this->actualOpponentLife . "\n";
            } else {
                $characterArmor = $this->playerStats->get(StatType::ARMOR->value);
                $damage -= ($damage - $characterArmor) > 0 ? $characterArmor : $damage;
                $this->actualPlayerLife -= $damage;
                //echo "player takes " . $damage . " current life : " . $this->actualPlayerLife . "\n";
            }
        }

        $action->setDamage($damage);

        if($this->fightIsOver())
        {
            $this->fight->setPlayerWin($this->actualPlayerLife > 0);

            $reward = new Reward();
            $reward->generate($this->fight, $this->fight->getPlayerWin());
            $reward->setFight($this->fight);
            $this->fight->setReward($reward);
        }

        return $action;
    }

    public function fightIsOver() : bool
    {
        if ($this->actualPlayerLife <= 0 || $this->actualOpponentLife <= 0)
        {
            return true;
        } else {
            return false;
        }
    }

    public function getFight(): Fight
    {
        return $this->fight;
    }

    public function getReward(): Reward
    {
        return $this->fight->getReward();
    }
}