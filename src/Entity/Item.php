<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ItemRepository;
use App\Trait\EntityItemTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\MappedSuperclass;
use JMS\Serializer\Annotation\Groups;

#[Entity(repositoryClass: ItemRepository::class)]
class Item extends BaseItem
{
    #[Groups(['playerInventory', 'shopList'])]
    #[Column(type: Types::INTEGER)]
    protected int $cost;

    public function getCost(): int
    {
        return $this->cost;
    }

    public function setCost(int $cost): void
    {
        $this->cost = $cost;
    }
}