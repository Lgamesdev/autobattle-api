<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\EquipmentSlot;
use App\Exception\CharacterEquipmentException;
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
use Exception;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\MaxDepth;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[Entity(repositoryClass: GearRepository::class)]
#[UniqueEntity(
    fields: ['character', 'equipment'],
    message: 'This character already got an equipment in this slot'
)]
class Gear
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    #[Exclude]
    private ?int $id = null;

    #[ManyToOne(targetEntity: UserCharacter::class, inversedBy: 'gear')]
    #[JoinColumn(name: 'character_id', referencedColumnName: 'id')]
    private UserCharacter $character;

    #[Groups(['gear', 'fighter', 'opponent_fighter'])]
    #[ManyToMany(targetEntity: CharacterEquipment::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[JoinTable(name: 'gear_character_equipments')]
    #[JoinColumn(name: 'gear_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'character_equipment_id', referencedColumnName: 'id', unique: true)]
    private Collection $equipments;

    public function __construct()
    {
        $this->equipments = new ArrayCollection();
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

    public function getCharacterEquipments(): ArrayCollection|Collection
    {
        return $this->equipments;
    }

    /**
     * @throws CharacterEquipmentException
     */
    public function equip(CharacterEquipment $characterEquipment): void
    {
        if($characterEquipment->getItem()->getRequiredLevel() > $this->character->getLevel())
        {
            throw new CharacterEquipmentException("Character level is too low");
        }

        $matchedEquipments = $this->equipments->filter(function($element) use ($characterEquipment) {
            return $element->getEquipmentSlot() === $characterEquipment->getEquipmentSlot();
        });

        $this->character->getInventory()->getItems()->removeElement($characterEquipment);

        if($matchedEquipments->count() > 0) {
            $oldEquipment = $matchedEquipments->first();
            $this->character->addToInventory($oldEquipment);

            $this->equipments[$matchedEquipments->key()] = $characterEquipment;
        } else {
            $this->equipments[] = $characterEquipment;
        }

        $characterEquipment->setCharacter($this->character);
    }

    public function unEquip(CharacterEquipment $characterEquipment): void
    {
        $this->equipments->removeElement($characterEquipment);
        $this->character->addToInventory($characterEquipment);
    }
}
