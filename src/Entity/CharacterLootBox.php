<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ItemQuality;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use JMS\Serializer\Annotation\Groups;

#[Entity()]
class CharacterLootBox extends BaseCharacterItem
{
    #[Groups(['playerInventory'])]
    #[ManyToOne(targetEntity: Item::class)]
    #[JoinColumn(name: 'lootBox_id', referencedColumnName: 'id')]
    protected LootBox $item;

    #[Groups(['lootBox'])]
    #[ManyToOne(targetEntity: Reward::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(name: 'reward_id', referencedColumnName: 'id')]
    private Reward $reward;

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