<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Symfony\Component\Serializer\Annotation\Groups;

#[Entity(repositoryClass: ItemRepository::class)]
#[InheritanceType('JOINED')]
#[DiscriminatorColumn(name: 'type', type: Types::STRING)]
#[DiscriminatorMap(['item' => Item::class, 'equipment' => Equipment::class])]
class Item
{
    #[Groups('inventory')]
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    protected ?int $id = null;

    #[Groups(['inventory', 'characterEquipment'])]
    #[Column(type: Types::STRING, unique: true)]
    protected string $name;

    #[Groups(['inventory', 'characterEquipment'])]
    #[Column(type: Types::STRING)]
    protected string $iconPath;

    #[Groups('inventory')]
    #[Column(type: Types::BOOLEAN)]
    protected bool $isDefaultItem = true;

    #[Groups('inventory')]
    #[Column(type: Types::INTEGER)]
    protected int $cost;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getIconPath(): string
    {
        return $this->iconPath;
    }

    public function setIconPath(string $iconPath): void
    {
        $this->iconPath = $iconPath;
    }

    public function isDefaultItem(): bool
    {
        return $this->isDefaultItem;
    }

    public function setIsDefaultItem(bool $isDefaultItem): void
    {
        $this->isDefaultItem = $isDefaultItem;
    }

    public function getCost(): int
    {
        return $this->cost;
    }

    public function setCost(int $cost): void
    {
        $this->cost = $cost;
    }
}