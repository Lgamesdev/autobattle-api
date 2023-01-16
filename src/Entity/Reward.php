<?php

namespace App\Entity;

use App\Enum\CurrencyType;
use App\Repository\ActionRepository;
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
use JMS\Serializer\Annotation\Groups;

#[Entity(repositoryClass: RewardRepository::class)]
class Reward
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ManyToOne(targetEntity: Fight::class, inversedBy: 'reward')]
    #[JoinColumn(name: 'fight_id', referencedColumnName: 'id')]
    private Fight $fight;

    #[Groups(['fight'])]
    #[Column(type: Types::INTEGER)]
    private int $experience = 0;

    #[Groups(['fight'])]
    #[Column(type: Types::INTEGER)]
    private int $ranking = 0;

    #[Groups(['fight'])]
    #[ManyToMany(targetEntity: Currency::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[JoinTable(name: 'reward_currencies')]
    #[JoinColumn(name: 'reward_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'currency_id', referencedColumnName: 'id', unique: true)]
    private Collection $currencies;

    #[Groups(['fight'])]
    #[ManyToMany(targetEntity: Item::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[JoinTable(name: 'reward_items')]
    #[JoinColumn(name: 'reward_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'item_id', referencedColumnName: 'id', unique: true)]
    private Collection $items;

    public function __construct()
    {
        $this->currencies = new ArrayCollection();
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFight(): Fight
    {
        return $this->fight;
    }

    public function setFight(Fight $fight): void
    {
        $this->fight = $fight;
    }

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

    public function addItem(Item $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
        }
        return $this;
    }

    public function generate(Fight $fight, bool $playerWin): void
    {
        $character = $fight->getCharacter();
        $opponent = $fight->getOpponent();

        $actualLevel = $character->getLevel();
        $passedLevel = $opponent->getLevel();

        $actualRank = $character->getRanking();
        $passedRank = $opponent->getRanking();

        $amount = $playerWin ? $actualLevel * 12 : $actualLevel * 3;

        $multiplier = 1 +
            (($actualLevel - $passedLevel) * 0.01)
            + (($actualRank - $passedRank) * 0.01) ;
        $amount = Round($amount * $multiplier);

        if(!$character->isMaxLevel()) {
            $this->setExperience($amount);
        } else {
            $this->setExperience(0);
        }

        if(!$character->isMaxRank()) {
            $this->setRanking($amount  * 0.1);
        } else {
            $this->setRanking(0);
        }

        $currency = new Currency();
        $currency->setCurrency(CurrencyType::GOLD);
        $currency->setAmount(Round($amount * 0.5));
        $this->addCurrency($currency);

        //Todo Items

        //
        $character->addExperience($this->experience);
        $character->addRanking(Round($this->ranking));
        $character->getWallet()->addCurrency($currency);
    }

}