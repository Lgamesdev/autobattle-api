<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Validator\Constraints as Assert;

#[Entity]
class Body
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[OneToOne(mappedBy: 'body', targetEntity: User::class)]
    private User $user;

    #[Column(type: Types::BOOLEAN)]
    private bool $isMaleGender = true;

    #[Column(type: Types::INTEGER)]
    private int $hairIndex = 0;

    #[Column(type: Types::INTEGER)]
    private int $moustacheIndex = 0;

    #[Column(type: Types::INTEGER)]
    private int $beardIndex = 0;

    #[Column(type: Types::STRING)]
    #[Assert\CssColor(
        formats: Assert\CssColor::HEX_LONG,
        message: 'The hair color must be 6-character hexadecimal color.'
    )]
    private string $hairColor = '#564336';

    #[Column(type: Types::STRING)]
    #[Assert\CssColor(
        formats: Assert\CssColor::HEX_LONG,
        message: 'The skin color must be 6-character hexadecimal color.'
    )]
    private string $skinColor = '#D8C19F';

    #[Column(type: Types::STRING)]
    #[Assert\CssColor(
        formats: Assert\CssColor::HEX_LONG,
        message: 'The chest color must be 6-character hexadecimal color.'
    )]
    private string $chestColor = '#dc0505';

    #[Column(type: Types::STRING)]
    #[Assert\CssColor(
        formats: Assert\CssColor::HEX_LONG,
        message: 'The body secondary color must be 6-character hexadecimal color.'
    )]
    private string $beltColor = '#c27101';

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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getHairIndex(): int
    {
        return $this->hairIndex;
    }

    public function setHairIndex(int $hairIndex): void
    {
        $this->hairIndex = $hairIndex;
    }

    public function getBeardIndex(): int
    {
        return $this->beardIndex;
    }

    public function setBeardIndex(int $beardIndex): void
    {
        $this->beardIndex = $beardIndex;
    }

    public function getBodyIndex(): int
    {
        return $this->bodyIndex;
    }

    public function setBodyIndex(int $bodyIndex): void
    {
        $this->bodyIndex = $bodyIndex;
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

    public function getBodyPrimaryColor(): string
    {
        return $this->bodyPrimaryColor;
    }

    public function setBodyPrimaryColor(string $bodyPrimaryColor): void
    {
        $this->bodyPrimaryColor = $bodyPrimaryColor;
    }

    public function getBodySecondaryColor(): string
    {
        return $this->bodySecondaryColor;
    }

    public function setBodySecondaryColor(string $bodySecondaryColor): void
    {
        $this->bodySecondaryColor = $bodySecondaryColor;
    }

    public function setRandomCustomization()
    {
        $skinColorArray = [
            "FFE9C6",
            "FFD8A0",
            "D8C19F",
            "D8AC6C",
            "D89774",
            "D1925F",
            "BF8759",
            "86644C",
            "3D2D22",
        ];

        $hairColorArray = [
            "503D30",
            "D4B60C",
            "5B4636",
            "000000",
            "5B5B5B",
            "BCBCBC",
            "564336",
        ];

        $this->setSkinColor('#' . $skinColorArray[array_rand($skinColorArray)]);
        $this->setHairColor('#' . $hairColorArray[array_rand($hairColorArray)]);

        //Random gender
        $this->isMaleGender = rand(0, 100) < 50;

        if($this->isMaleGender)
        {
            //30% chances to be bald
            if(rand(0, 100) < 70)
            {
                $this->setHairIndex(rand(1, 4));
            } else {
                $this->setHairIndex(0);
            }
            //30% chances to be with no beard
            if(rand(0, 100) < 70)
            {
                $this->setBeardIndex(rand(1, 4));
            } else {
                $this->setBeardIndex(0);
            }
        }

        $this->setBodyIndex(rand(0, 4));
    }

    public function toArray(): array
    {
        return [
            "hairIndex" => $this->getHairIndex(),
            "beardIndex" => $this->getBeardIndex(),
            "bodyIndex" => $this->getBodyIndex(),
            "hairColor" => $this->getHairColor(),
            "skinColor" => $this->getSkinColor(),
            "bodyPrimaryColor" => $this->getBodyPrimaryColor(),
            "bodySecondaryColor" => $this->getBodySecondaryColor(),
        ];
    }
}