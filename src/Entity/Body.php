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

    #[Column(type: Types::INTEGER)]
    private int $hairIndex = 0;

    #[Column(type: Types::INTEGER)]
    private int $beardIndex = 0;

    #[Column(type: Types::INTEGER)]
    private int $bodyIndex = 0;

    #[Column(type: Types::STRING)]
    #[Assert\CssColor(
        formats: Assert\CssColor::HEX_LONG,
        message: 'The hair color must be 6-character hexadecimal color.'
    )]
    private string $hairColor = '#564336';

    #[Column(type: Types::STRING)]
    #[Assert\CssColor(
        formats: Assert\CssColor::HEX_LONG,
        message: 'The beard color must be 6-character hexadecimal color.'
    )]
    private string $skinColor = '#D8C19F';

    #[Column(type: Types::STRING)]
    #[Assert\CssColor(
        formats: Assert\CssColor::HEX_LONG,
        message: 'The body primary color must be 6-character hexadecimal color.'
    )]
    private string $bodyPrimaryColor = '#dc0505';

    #[Column(type: Types::STRING)]
    #[Assert\CssColor(
        formats: Assert\CssColor::HEX_LONG,
        message: 'The body secondary color must be 6-character hexadecimal color.'
    )]
    private string $bodySecondaryColor = '#c27101';

    public function getId(): ?int
    {
        return $this->id;
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
}