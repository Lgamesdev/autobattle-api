<?php

namespace App\Entity;

use App\Enum\FightActionType;
use App\Enum\FightType;
use App\Enum\StatType;
use App\Exception\FightException;
use App\Repository\FightRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
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

    private int $actualPlayerEnergy;
    private int $actualOpponentEnergy;

    private UserCharacter $player;
    private Fighter $opponent;

    private ArrayCollection $playerStats;
    private ArrayCollection $opponentStats;

    private Fight $fight;

    private bool $isPlayerTurn;

    public function __construct(UserCharacter $player, Fighter $opponent, FightType $fightType)
    {
        $this->player = $player;
        $this->opponent = $opponent;

        $this->playerStats = $this->player->getFullStats();
        $this->opponentStats = $this->opponent->getFullStats();

        $this->actualPlayerLife = $this->playerStats->get(StatType::HEALTH->value);
        $this->actualOpponentLife = $this->opponentStats->get(StatType::HEALTH->value);

        $this->actualPlayerEnergy = 0;
        $this->actualOpponentEnergy = 0;

        $this->fight = new Fight();
        $this->fight->setFightType($fightType);
        $this->fight->setCharacter($player);
        $this->fight->setOpponent($opponent);

        $this->isPlayerTurn = ($this->playerStats->get(StatType::AGILITY->value) /
           ($this->playerStats->get(StatType::AGILITY->value) + $this->opponentStats->get(StatType::AGILITY->value)) * 100) >= rand(0, 100);

        if(!$this->isPlayerTurn)
        {
            $this->fight->addAction($this->createAction(FightActionType::ATTACK));
        }
    }

    /**
     * @throws FightException
     */
    public function attack(FightActionType $actionType): ArrayCollection|Collection
    {
        $actions = new ArrayCollection();

        if(!$this->fight->getActions()->isEmpty()) {
            $this->isPlayerTurn = true;
        }

        if(!$this->fightIsOver()) {
            //1rst attack action by player
            if($actionType == FightActionType::SPECIAL_ATTACK
                && $this->actualPlayerEnergy < 100)
            {
                throw new FightException('not enough energy to launch a special attack', 403);
            }

            if($actionType == FightActionType::PARRY)
            {
                $actions->add($this->createAction($actionType));

                $this->fight->addActions($actions);
                return $actions;
            } else {
                $actions->add($this->createAction($actionType));

                if (!$this->fightIsOver()) {
                    //2nd attack action
                    if (!$this->isPlayerTurn) {
                        $this->isPlayerTurn = !(rand(0, 100) < $this->opponentStats->get(StatType::AGILITY->value));
                    } else {
                        //Chance to be player turn
                        $this->isPlayerTurn = rand(0, 100) < $this->playerStats->get(StatType::AGILITY->value);
                    }

                    //Redo same action type if it's the player turn
                    if ($this->isPlayerTurn) {
                        $actions->add($this->createAction($actionType));
                    } else {
                        $actions->add($this->createAction($this->opponentAttack()));
                    }

                    if (!$this->fightIsOver()) {
                        //Check if player hitted 2 times (max) in a row, else opponent will try a second hit
                        if ($this->isPlayerTurn) {
                            //3rd attack action by opponent (forced)
                            $this->isPlayerTurn = false;
                            $actions->add($this->createAction($this->opponentAttack()));

                            if (!$this->fightIsOver()) {
                                //4rth attack action by opponent if he roll speed
                                $this->isPlayerTurn = rand(0, 100) < $this->opponentStats->get(StatType::AGILITY->value);

                                //Check if it's opponent turn or skip
                                if (!$this->isPlayerTurn) {
                                    $actions->add($this->createAction($this->opponentAttack()));

                                    if ($this->fightIsOver()) {
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
                            $this->isPlayerTurn = !(rand(0, 100) < $this->opponentStats->get(StatType::AGILITY->value));

                            //Check if it's opponent turn or skip
                            if (!$this->isPlayerTurn) {
                                $actions->add($this->createAction($this->opponentAttack()));

                                if ($this->fightIsOver()) {
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
        }

        $this->fight->addActions($actions);
        return $actions;
    }

    private function opponentAttack(): FightActionType
    {
        if ($this->actualOpponentEnergy == 100) {
            return FightActionType::SPECIAL_ATTACK;
        } else {
            return FightActionType::ATTACK;
        }
    }

    private function createAction(FightActionType $actionType): Action
    {
        //echo "create action \n";

        $action = new Action();
        $action->setPlayerTeam($this->isPlayerTurn);
        $action->setActionType($actionType);

        $damage = 0;
        $energyGained = 0;
        if($actionType == FightActionType::PARRY)
        {
            $energyGained = 22;
            $action->setDodged(false);

            if (rand(0, 100) < ($action->isPlayerTeam() ? $this->playerStats->get(StatType::LUCK->value) : $this->opponentStats->get(StatType::LUCK->value))) {
                $action->setCriticalHit(true);
                if (rand(0, 100) < ($action->isPlayerTeam() ? $this->opponentStats->get(StatType::LUCK->value) : $this->playerStats->get(StatType::LUCK->value))) {
                    $damage = $action->isPlayerTeam() ?
                        $this->opponentStats->get(StatType::STRENGTH->value)
                        : $this->playerStats->get(StatType::STRENGTH->value);
                }
            } else {
                $damage = $action->isPlayerTeam() ?
                    intval($this->opponentStats->get(StatType::STRENGTH->value) / 2)
                    : intval($this->playerStats->get(StatType::STRENGTH->value) / 2);
            }

            if ($action->isPlayerTeam()) {
                $characterArmor = $this->playerStats->get(StatType::ARMOR->value);
                $damage -= ($damage - $characterArmor) > 0 ? $characterArmor : $damage;
                $this->actualPlayerLife -= $damage;
                //echo "player parry " . $damage . " current life : " . $this->actualPlayerLife . "\n";
            } else {
                $opponentArmor = $this->opponentStats->get(StatType::ARMOR->value);
                $damage -= ($damage - $opponentArmor) > 0 ? $opponentArmor : $damage;
                $this->actualOpponentLife -= $damage;
                //echo "opponent parry " . $damage . " current life : " . $this->actualOpponentLife . "\n";
            }

        } else {
            if (rand(0, 100) < ($action->isPlayerTeam() ? $this->playerStats->get(StatType::AGILITY->value) : $this->opponentStats->get(StatType::AGILITY->value))) {
                $action->setDodged(true);
            } else if (rand(0, 100) < ($action->isPlayerTeam() ? $this->playerStats->get(StatType::LUCK->value) : $this->opponentStats->get(StatType::LUCK->value))) {
                $action->setCriticalHit(true);
                switch ($actionType) {
                    case FightActionType::ATTACK:
                        $energyGained = 26;
                        $damage = ($action->isPlayerTeam() ?
                                $this->playerStats->get(StatType::STRENGTH->value)
                                : $this->opponentStats->get(StatType::STRENGTH->value)) * 2;
                        break;
                    case FightActionType::SPECIAL_ATTACK:
                        $damage = ($action->isPlayerTeam() ?
                                $this->playerStats->get(StatType::STRENGTH->value) * 3
                                : $this->opponentStats->get(StatType::STRENGTH->value) * 3) * 2;

                        if ($action->isPlayerTeam()) {
                            $this->actualPlayerEnergy = 0;
                        } else {
                            $this->actualOpponentEnergy = 0;
                        }
                        break;
                }
            } else {
                switch ($actionType) {
                    case FightActionType::ATTACK:
                        $energyGained = 19;
                        $damage = $action->isPlayerTeam() ?
                            $this->playerStats->get(StatType::STRENGTH->value)
                            : $this->opponentStats->get(StatType::STRENGTH->value);
                        break;
                    case FightActionType::SPECIAL_ATTACK:
                        $damage = $action->isPlayerTeam() ?
                            $this->playerStats->get(StatType::STRENGTH->value) * 3
                            : $this->opponentStats->get(StatType::STRENGTH->value) * 3;
                        if ($action->isPlayerTeam()) {
                            $this->actualPlayerEnergy = 0;
                        } else {
                            $this->actualOpponentEnergy = 0;
                        }
                        break;
                }
            }

            if (!$action->isDodged()) {
                if ($action->isPlayerTeam()) {
                    $opponentArmor = $this->opponentStats->get(StatType::ARMOR->value);
                    $damage -= ($damage - $opponentArmor) > 0 ? $opponentArmor : $damage;
                    $this->actualOpponentLife -= $damage;
                    //echo "opponent takes " . $damage . " current life : " . $this->actualOpponentLife . "\n";
                } else {
                    $energyGained = 0;
                    $characterArmor = $this->playerStats->get(StatType::ARMOR->value);
                    $damage -= ($damage - $characterArmor) > 0 ? $characterArmor : $damage;
                    $this->actualPlayerLife -= $damage;
                    //echo "player takes " . $damage . " current life : " . $this->actualPlayerLife . "\n";
                }
            }
        }
        if($action->isPlayerTeam())
        {
            $energyGained = ($this->actualPlayerEnergy + $energyGained) > 100 ?
                100 - $this->actualPlayerEnergy
                : $energyGained;
            $this->actualPlayerEnergy += $energyGained;
        } else {
            $energyGained = ($this->actualOpponentEnergy + $energyGained) > 100 ?
                100 - $this->actualOpponentEnergy
                : $energyGained;
            $this->actualOpponentEnergy += $energyGained;
        }

        $action->setDamage($damage);
        $action->setEnergyGained($energyGained);

        if($this->fightIsOver())
        {
            $this->fight->setPlayerWin($this->actualPlayerLife > 0);

            $reward = new Reward();
            $reward->generate($this->fight);
            $this->fight->setReward($reward);
        }

        return $action;
    }

    private function getLastPlayerAction(): ?Action
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('playerTurn', true));

        return $this->fight->getActions()->matching($criteria)->last();
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

    public function finishFight(): Fight
    {
        while(!$this->fightIsOver())
        {
            $this->fight->addAction($this->createAction(FightActionType::ATTACK));
            $this->isPlayerTurn = !$this->isPlayerTurn;
        }

        return $this->fight;
    }
}