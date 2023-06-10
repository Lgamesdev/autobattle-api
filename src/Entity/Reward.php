<?php

namespace App\Entity;

use App\Enum\CurrencyType;
use App\Enum\FightType;
use App\Enum\RewardType;
use App\Repository\RewardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;

#[Entity(repositoryClass: RewardRepository::class)]
class Reward
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Groups(['fight'])]
    #[Column(type: Types::INTEGER)]
    private int $experience = 0;

    #[Groups(['fight'])]
    #[Column(type: Types::INTEGER)]
    private int $ranking = 0;

    #[Groups(['fight', 'lootBox'])]
    #[ManyToMany(targetEntity: Currency::class, cascade: ['persist'], orphanRemoval: true)]
    #[JoinTable(name: 'reward_currencies')]
    #[JoinColumn(name: 'reward_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'currency_id', referencedColumnName: 'id', unique: true)]
    private Collection $currencies;

    #[Groups(['fight', 'lootBox'])]
    #[ManyToMany(targetEntity: BaseCharacterItem::class, orphanRemoval: true)]
    #[JoinTable(name: 'reward_items')]
    #[JoinColumn(name: 'reward_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'item_id', referencedColumnName: 'id', unique: true)]
    private Collection $items;

    public function __construct(ArrayCollection $items = null)
    {
        $this->currencies = new ArrayCollection();

        if($items != null)
        {
            $this->items = $items;
        } else {
            $this->items = new ArrayCollection();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

/*    public function getFight(): Fight
    {
        return $this->fight;
    }

    public function setFight(Fight $fight): void
    {
        $this->fight = $fight;
    }*/

/*    public function getRewardType(): RewardType
    {
        return $this->rewardType;
    }

    public function setRewardType(RewardType $rewardType): void
    {
        $this->rewardType = $rewardType;
    }

    #[Groups(['fight', 'lootBox'])]
    #[VirtualProperty]
    #[SerializedName('rewardType')]
    public function getRewardTypeValue(): string
    {
        return $this->rewardType->value;
    }*/

/*    public function getPlayerWin(): bool
    {
        return $this->playerWin;
    }

    public function setPlayerWin(bool $playerWin): void
    {
        $this->playerWin = $playerWin;
    }*/

    public function getExperience(): int
    {
        return $this->experience;
    }

    public function setExperience(int $experience): void
    {
        $this->experience = $experience;
    }

    public function getRanking(): int
    {
        return $this->ranking;
    }

    public function setRanking(int $ranking): void
    {
        $this->ranking = $ranking;
    }

    public function getCurrencies(): ArrayCollection|Collection
    {
        return $this->currencies;
    }

    public function addCurrency(Currency $currency): self
    {
        if (!$this->currencies->contains($currency)) {
            $this->currencies->add($currency);
        }
        return $this;
    }

    public function getItems(): ArrayCollection|Collection
    {
        return $this->items;
    }

    public function setItems(ArrayCollection $items)
    {
        $this->items = $items;
    }

    public function addItem(BaseCharacterItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
        }
        return $this;
    }

    public function generate(Fight $fight): void
    {
        $character = $fight->getCharacter();
        $opponent = $fight->getOpponent();

        $amount = $fight->isPlayerWin() ? 138 : 69;

        //Experience
        $actualLevel = $character->getLevel();
        $passedLevel = $opponent->getLevel();

        $expAmount = round(($amount / log(UserCharacter::MAX_LEVEL)) * log($actualLevel) + 20);

        if(!$character->isMaxLevel()) {
            if($fight->getFightType() == FightType::PVP) {
                $this->setExperience($expAmount);
            } else {
                $this->setExperience($expAmount * 2.5);
            }
        } else {
            $this->setExperience(0);
        }

        //Gold
        if($fight->getFightType() == FightType::PVP) {
            $goldAmount = round(($expAmount * ((($passedLevel / $actualLevel) + (($opponent->getRanking() / $character->getRanking()) * 1.25)) / 2)) * 1.10);
        } else {
            $goldAmount = 500 * $passedLevel;
        }

        $currency = new Currency();
        $currency->setCurrency(CurrencyType::GOLD);
        if($fight->isPlayerWin()) {
            $currency->setAmount($goldAmount);
        } else {
            $currency->setAmount(0);
        }
        $this->addCurrency($currency);

        //Ranking
        if($fight->getFightType() == FightType::PVP) {
            $actualRank = $character->getRanking();
            $passedRank = $opponent->getRanking();

            $rankAmount = round(15 * ((($passedLevel / $actualLevel) + (($passedRank / $actualRank) * 1.2)) / 2));
            if (!$fight->isPlayerWin()) {
                $rankAmount = -$rankAmount;
            }

            if ($fight->isPlayerWin()) {
                if ($character->isMaxRank()) {
                    $this->setRanking(0);
                } else {
                    if (($character->getRanking() + $rankAmount) < UserCharacter::MAX_RANK) {
                        $this->setRanking($rankAmount);
                    } else {
                        $this->setRanking(UserCharacter::MAX_RANK - $character->getRanking());
                    }
                }
            } else {
                if (($character->getRanking() - $rankAmount) < 0) {
                    $this->setRanking(0);
                } else {
                    $this->setRanking($rankAmount);
                }
            }
        } else {
            $this->setRanking(0);
        }

        //Todo Items

        /*echo 'experience amount : ' . $expAmount . "\n";
        echo 'gold amount : ' . $goldAmount . "\n";
        echo 'rank amount : ' . $rankAmount . "\n";*/
    }
}