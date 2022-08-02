<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EquipmentSlotRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\Serializer\Annotation\Groups;

#[Entity(repositoryClass: EquipmentSlotRepository::class)]
class EquipmentSlot
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Groups('characterEquipment')]
    #[Column(type: Types::STRING)]
    #[JoinColumn(unique: true)]
    private string $label;

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
}