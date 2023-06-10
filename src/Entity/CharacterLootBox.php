<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use JMS\Serializer\Annotation\Groups;

#[Entity()]
class CharacterLootBox extends BaseCharacterItem
{
    #[Groups(['playerInventory', 'lootBox'])]
    #[ManyToOne(targetEntity: Item::class)]
    #[JoinColumn(name: 'lootBox_id', referencedColumnName: 'id')]
    protected LootBox $item;

    #[Groups(['lootBox'])]
    /*#[ManyToOne(targetEntity: Reward::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(name: 'reward_id', referencedColumnName: 'id', unique: true, onDelete: "CASCADE")]*/
    private Reward $reward;

    public function __construct(LootBox $item = null)
    {
        $this->reward = new Reward();

        if($item != null) {
            $this->item = $item;
        } else {
            $this->item = new LootBox();
        }
    }

    public function getItem(): LootBox
    {
        return $this->item;
    }

    public function setItem(LootBox $item): void
    {
        $this->item = $item;
    }

    /**
     * @return Reward
     */
    public function getReward(): Reward
    {
        return $this->reward;
    }

    /**
     * @param Reward $reward
     */
    public function setReward(Reward $reward): void
    {
        $this->reward = $reward;
    }
}