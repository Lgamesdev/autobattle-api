<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EquipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Serializer\Annotation\Groups;

#[Entity(repositoryClass: EquipmentRepository::class)]
class Equipment extends Item
{
    /**
     * Collection of Statistic
     * @var Collection
     */
    #[Groups('characterEquipment')]
    #[OneToMany(mappedBy: 'equipment', targetEntity: EquipmentStat::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $stats;

    #[Groups('characterEquipment')]
    #[ManyToOne(targetEntity: EquipmentSlot::class)]
    #[JoinColumn(name: 'equipmentSlot_id', referencedColumnName: 'id')]
    private EquipmentSlot $equipmentSlot;

    #[Groups('characterEquipment')]
    #[Column(type: Types::STRING)]
    private string $spritePath;

    protected bool $isDefaultItem = false;

    public function __construct()
    {
        $this->stats = new ArrayCollection();
    }

    public function getStats(): Collection
    {
        return $this->stats;
    }

    public function addStat(EquipmentStat $stat): self
    {
        if (!$this->stats->contains($stat)) {
            $this->stats[] = $stat;
            $stat->setEquipment($this);
        }

        return $this;
    }

    public function stat(Statistic $stat, int $value) : void
    {
        $newStat = new EquipmentStat();
        $newStat->setStat($stat);
        $newStat->setValue($value);
        $this->addStat($newStat);
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