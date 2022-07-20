<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EquipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity(repositoryClass: EquipmentRepository::class)]
class Equipment extends Item
{
    /**
     * Collection of Stat
     * @var Collection
     */
    #[OneToMany(mappedBy: 'equipment', targetEntity: Stat::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $stats;

    #[ManyToOne(targetEntity: EquipmentSlot::class, inversedBy: 'equipments')]
    #[JoinColumn(name: 'equipmentSlot_id', nullable: false)]
    private EquipmentSlot $equipmentSlot;

    #[Column(type: Types::STRING)]
    private string $spritePath;

    protected bool $isDefaultItem = false;

    public function __construct(EquipmentSlot $equipmentSlot)
    {
        $this->stats = new ArrayCollection();
        $this->equipmentSlot = $equipmentSlot;
    }

    public function getStats(): Collection
    {
        return $this->stats;
    }

    public function addStat(Stat $stat): self
    {
        if (!$this->stats->contains($stat)) {
            $this->stats[] = $stat;
            $stat->setEquipment($this);
        }

        return $this;
    }

    public function stat(StatType $type, int $value) : void
    {
        $newStat = new Stat($type, $value);
        $newStat->setEquipment($this);
        $this->stats->add($newStat);
    }

    public function getEquipmentSlot(): EquipmentSlot
    {
        return $this->equipmentSlot;
    }

    public function setEquipmentSlot(EquipmentSlot $equipmentSlot): void
    {
        $this->equipmentSlot = $equipmentSlot;
    }

    public function getSpritePath(): string
    {
        return $this->spritePath;
    }

    public function setSpritePath(string $spritePath): void
    {
        $this->spritePath = $spritePath;
    }
}