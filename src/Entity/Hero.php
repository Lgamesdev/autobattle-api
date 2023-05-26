<?php

namespace App\Entity;

use App\Enum\CurrencyType;
use App\Enum\StatType;
use App\Exception\CharacterEquipmentException;
use App\Exception\ShopException;
use App\Exception\UserCharacterException;
use App\Repository\CharacterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Exception;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[Entity()]
/*#[UniqueEntity(fields: 'name', message: 'This character\'s name is already used.')]*/
class Hero extends Fighter
{
    /*#[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    #[Exclude]
    private ?int $id = null;*/

    #[Column(type: Types::STRING, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 25)]
    private string $username;

    /*#[Groups(['fighter', 'opponent_fighter'])]
    #[OneToOne(mappedBy: 'character', targetEntity: Gear::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Gear $gear;*/

/*    #[OneToOne(mappedBy: 'character', targetEntity: Inventory::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Inventory $inventory;*/

    public function __construct()
    {
        parent::__construct();

        /*$this->gear = new Gear();
        $this->gear->setCharacter($this);*/
    }

    #[Groups(['fighter', 'opponent_fighter', 'message'])]
    #[VirtualProperty]
    #[SerializedName('username')]
    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /*public function getGear(): Gear
    {
        return $this->gear;
    }*/

    public function isMaxLevel(): bool
    {
        return $this->level == self::MAX_LEVEL;
    }

    #[Groups(['fighter', 'opponent_fighter'])]
    #[VirtualProperty]
    #[SerializedName('fullStats')]
    public function getFullStats(): ArrayCollection
    {
        $fullStats = new ArrayCollection();

        /** @var CharacterStat $characterStat */
        foreach ($this->stats as $characterStat) {
            $fullStats[$characterStat->getStatType()] = $characterStat->getValue();
        }

//        /** @var CharacterEquipment $equipment */
//        foreach ($this->gear->getCharacterEquipments() as $equipment)
//        {
//            /** @var Equipment $item */
//            $item = $equipment->getItem();
//
//            foreach ($item->getStats() as $equipmentStat)
//            {
//                if(!$fullStats->containsKey($equipmentStat->getStatType())) {
//                    $fullStats[$equipmentStat->getStatType()] = $equipmentStat->getValue();
//                } else {
//                    $fullStats[$equipmentStat->getStatType()] += $equipmentStat->getValue();
//                }
//            }
//            /** @var CharacterEquipmentModifier $equipmentModifier */
//            foreach ($equipment->getModifiers() as $equipmentModifier)
//            {
//                if(!$fullStats->containsKey($equipmentModifier->getStatType())) {
//                    $fullStats[$equipmentModifier->getStatType()] = $equipmentModifier->getValue();
//                } else {
//                    $fullStats[$equipmentModifier->getStatType()] += $equipmentModifier->getValue();
//                }
//            }
//        }

        return $fullStats;
    }
}