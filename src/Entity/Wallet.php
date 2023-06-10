<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CurrencyType;
use App\Enum\ItemType;
use App\Exception\ShopException;
use App\Exception\UserCharacterException;
use App\Repository\WalletRepository;
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
use Doctrine\ORM\Mapping\OneToMany;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[Entity(repositoryClass: WalletRepository::class)]
#[UniqueEntity(
    fields: ['character', 'currency'],
    message: 'This character already got an amount of this currency'
)]
class Wallet
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    #[Exclude]
    private ?int $id = null;

    #[ManyToOne(targetEntity: UserCharacter::class, inversedBy: 'wallet')]
    #[JoinColumn(name: 'character_id', referencedColumnName: 'id')]
    private UserCharacter $character;

    #[Groups(['characterWallet', 'fighter'])]
    #[ManyToMany(targetEntity: Currency::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[JoinTable(name: 'wallet_currencies')]
    #[JoinColumn(name: 'wallet_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'currency_id', referencedColumnName: 'id', unique: true)]
    private Collection $currencies;

    public function __construct()
    {
        $this->currencies = new ArrayCollection();
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

    public function getCurrencies(): ArrayCollection|Collection
    {
        return $this->currencies;
    }

    public function addCurrency(Currency $currency): self
    {
        $currencyMatched = $this->currencies->filter(function($element) use ($currency) {
            return $element->getCurrency() === $currency->getCurrency();
        });

        if($currencyMatched->count() > 0) {
            $characterGold = $currencyMatched->first()->getAmount();
            $this->currencies[$currencyMatched->key()]->setAmount($characterGold + $currency->getAmount());
        } else {
            $this->currencies[] = $currency;
        }

        return $this;
    }

    /**
     * @throws ShopException
     * @throws UserCharacterException
     */
    public function tryBuy(Item|LootBox|Equipment $item): CharacterItem|CharacterLootBox|CharacterEquipment|null
    {
        $currencyMatched = $this->currencies->filter(function($element) {
            return $element->getCurrency() === CurrencyType::GOLD;
        });

        $characterGold = $currencyMatched->first()->getAmount();

        if ($characterGold >= $item->getCost()) {
            $this->currencies[$currencyMatched->key()]->setAmount($characterGold - $item->getCost());
            switch($item->getItemType())
            {
                case ItemType::LOOTBOX:
                    $characterLootBox = new CharacterLootBox($item);
                    $this->character->addToInventory($characterLootBox);
                    return $characterLootBox;

                case ItemType::EQUIPMENT:
                    $characterEquipment = new CharacterEquipment($item);
                    $this->character->addToInventory($characterEquipment);
                    return $characterEquipment;

                case ItemType::Item:
                default:
                    $characterItem = new CharacterItem($item);
                    $this->character->addToInventory($characterItem);
                    return $characterItem;
            }
        } else {
            throw new ShopException('Cannot afford item for ' . $item->getCost() . 'gold');
        }
    }

    /**
     * @throws UserCharacterException
     */
    public function sell(CharacterEquipment|CharacterLootBox|CharacterItem $characterItem): void
    {
        $currencyMatched = $this->currencies->filter(function($element) {
            return $element->getCurrency() === CurrencyType::GOLD;
        });

        $characterGold = $currencyMatched->first()->getAmount();
        $this->currencies[$currencyMatched->key()]->setAmount($characterGold + $characterItem->getItem()->getCost());
        $this->character->removeFromInventory($characterItem);
    }
}
