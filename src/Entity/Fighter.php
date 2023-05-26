<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\StatType;
use App\Repository\BaseCharacterItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Range;

#[Entity()]
#[InheritanceType('JOINED')]
#[DiscriminatorColumn(name: 'type', type: Types::STRING)]
#[DiscriminatorMap([
    'user_character' => UserCharacter::class,
    'hero' => Hero::class
])]
abstract class Fighter
{
    public const MAX_LEVEL = 100;

    #[Groups(['playerInventory', 'gear'])]
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    protected ?int $id = null;

    #[Groups(['fighter', 'opponent_fighter'])]
    #[Column(type: Types::INTEGER)]
    #[Range(notInRangeMessage: "minimum hero\'s level must be 1 at minimum", min: 1)]
    protected int $level = 1;

    #[Groups(['fighter', 'opponent_fighter'])]
    #[OneToOne(mappedBy: 'fighter', targetEntity: Body::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Body $body;

    #[Groups(['fighter', 'opponent_fighter', 'characterStat'])]
    #[OneToMany(mappedBy: 'fighter', targetEntity: CharacterStat::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $stats;

    public function __construct()
    {
        $this->body = new Body();
        $this->body->setFighter($this);

        $this->stats = new ArrayCollection();
    }

    function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getBody(): Body
    {
        return $this->body;
    }

    public function setBody(Body $body): void
    {
        $this->body->setBeardIndex($body->getBeardIndex());
        $this->body->setChestColor($body->getChestColor());
        $this->body->setHairColor($body->getHairColor());
        $this->body->setHairIndex($body->getHairIndex());
        $this->body->setIsMaleGender($body->isMaleGender());
        $this->body->setMoustacheIndex($body->getMoustacheIndex());
        $this->body->setShortColor($body->getShortColor());
        $this->body->setSkinColor($body->getSkinColor());
    }


    public function getStats(): ArrayCollection|Collection
    {
        return $this->stats;
    }

    public function addStat(CharacterStat $stat): self
    {
        $statsMatched = $this->stats->filter(function($element) use ($stat) {
            return $element->getStatType() === $stat->getStatType();
        });

        if($statsMatched->count() > 0) {
            $statValue = $statsMatched->first()->getValue();
            $this->stats[$statsMatched->key()]->setValue($statValue + $stat->getValue());
        } else {
            $this->stats[] = $stat;
        }

        return $this;
    }

    public function stat(StatType $stat, ?int $value) : CharacterStat
    {
        $newStat = new CharacterStat();
        if($value != null) {
            $newStat->setStat($stat);
            $newStat->setValue($value);
            $newStat->setFighter($this);
            $this->addStat($newStat);
        }
        return $newStat;
    }
}