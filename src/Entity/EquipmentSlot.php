<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EquipmentSlotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity(repositoryClass: EquipmentSlotRepository::class)]
class EquipmentSlot
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Column(type: Types::STRING)]
    #[JoinColumn(unique: true)]
    private string $label;

    #[OneToMany(mappedBy: 'equipmentSlot', targetEntity: Equipment::class)]
    private Collection $equipments;

    public function __construct()
    {
        $this->equipments  = new ArrayCollection();
    }

    function getId(): ?int {
        return $this->id;
    }

    function getLabel(): string {
        return $this->label;
    }

    function setLabel(string $label): self {
        $this->label = $label;
        return $this;
    }

    public function getEquipments(): ArrayCollection|Collection
    {
        return $this->equipments;
    }
}