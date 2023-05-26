<?php

namespace App\Entity;

use App\Repository\BodyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[Entity(repositoryClass: BodyRepository::class)]
class Body
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    #[Exclude]
    private ?int $id = null;

    #[OneToOne(inversedBy: 'body', targetEntity: Fighter::class)]
    #[JoinColumn(name: 'fighter_id', referencedColumnName: 'id')]
    private Fighter $fighter;

    #[Groups(['body', 'fighter', 'opponent_fighter'])]
    #[Column(type: Types::BOOLEAN)]
    private bool $isMaleGender = true;

    #[Groups(['body', 'fighter', 'opponent_fighter'])]
    #[Column(type: Types::INTEGER)]
    private int $hairIndex = 0;

    #[Groups(['body', 'fighter', 'opponent_fighter'])]
    #[Column(type: Types::INTEGER)]
    private int $moustacheIndex = 0;

    #[Groups(['body', 'fighter', 'opponent_fighter'])]
    #[Column(type: Types::INTEGER)]
    private int $beardIndex = 0;

    #[Groups(['body', 'fighter', 'opponent_fighter'])]
    #[Column(type: Types::STRING)]
    #[Assert\CssColor(
        formats: Assert\CssColor::HEX_LONG,
        message: 'The hair color must be 6-character hexadecimal color.'
    )]
    private string $hairColor = '#564336';

    #[Groups(['body', 'fighter', 'opponent_fighter'])]
    #[Column(type: Types::STRING)]
    #[Assert\CssColor(
        formats: Assert\CssColor::HEX_LONG,
        message: 'The skin color must be 6-character hexadecimal color.'
    )]
    private string $skinColor = '#D8C19F';

    #[Groups(['body', 'fighter', 'opponent_fighter'])]
    #[Column(type: Types::STRING)]
    #[Assert\CssColor(
        formats: Assert\CssColor::HEX_LONG,
        message: 'The chest color must be 6-character hexadecimal color.'
    )]
    private string $chestColor = '#dc0505';

    #[Groups(['body', 'fighter', 'opponent_fighter'])]
    #[Column(type: Types::STRING)]
    #[Assert\CssColor(
        formats: Assert\CssColor::HEX_LONG,
        message: 'The short color must be 6-character hexadecimal color.'
    )]
    private string $shortColor = '#dc0505';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFighter(): Fighter
    {
        return $this->fighter;
    }

    public function setFighter(Fighter $fighter): void
    {
        $this->fighter = $fighter;
    }

    public function isMaleGender(): bool
    {
        return $this->isMaleGender;
    }

    public function setIsMaleGender(bool $isMaleGender): void
    {
        $this->isMaleGender = $isMaleGender;
    }

    public function getHairIndex(): int
    {
        return $this->hairIndex;
    }

    public function setHairIndex(int $hairIndex): void
    {
        $this->hairIndex = $hairIndex;
    }

    public function getMoustacheIndex(): int
    {
        return $this->moustacheIndex;
    }

    public function setMoustacheIndex(int $moustacheIndex): void
    {
        $this->moustacheIndex = $moustacheIndex;
    }

    public function getBeardIndex(): int
    {
        return $this->beardIndex;
    }

    public function setBeardIndex(int $beardIndex): void
    {
        $this->beardIndex = $beardIndex;
    }

    public function getHairColor(): string
    {
        return $this->hairColor;
    }

    public function setHairColor(string $hairColor): void
    {
        $this->hairColor = $hairColor;
    }

    public function getSkinColor(): string
    {
        return $this->skinColor;
    }

    public function setSkinColor(string $skinColor): void
    {
        $this->skinColor = $skinColor;
    }

    public function getChestColor(): string
    {
        return $this->chestColor;
    }

    public function setChestColor(string $chestColor): void
    {
        $this->chestColor = $chestColor;
    }

    public function getShortColor(): string
    {
        return $this->shortColor;
    }

    public function setShortColor(string $shortColor): void
    {
        $this->shortColor = $shortColor;
    }

    public function setRandomCustomization(): void
    {
        $skinColorArray = [
            "#FFE9C6",
            "#FFD8A0",
            "#D8C19F",
            "#D8AC6C",
            "#D89774",
            "#D1925F",
            "#BF8759",
            "#86644C",
            "#3D2D22",
        ];

        $hairColorArray = [
            "#503D30",
            "#D4B60C",
            "#5B4636",
            "#000000",
            "#5B5B5B",
            "#BCBCBC",
            "#564336",
        ];

        $this->setSkinColor($skinColorArray[array_rand($skinColorArray)]);
        $this->setHairColor($hairColorArray[array_rand($hairColorArray)]);

        //Random gender
        $this->setIsMaleGender(rand(0, 100) < 50);

        if($this->isMaleGender())
        {
            //30% chances to be bald
            if(rand(0, 100) < 70)
            {
                $this->setHairIndex(rand(1, 4));
            } else {
                $this->setHairIndex(0);
            }
            //30% chances to be with no moustache
            if(rand(0, 100) < 70)
            {
                $this->setMoustacheIndex(rand(1, 4));
            } else {
                $this->setMoustacheIndex(0);
            }
            //30% chances to be with no beard
            if(rand(0, 100) < 70)
            {
                $this->setBeardIndex(rand(1, 4));
            } else {
                $this->setBeardIndex(0);
            }
        }

    }
}