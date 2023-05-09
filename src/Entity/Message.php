<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;

#[Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    #[Exclude]
    private ?int $id = null;

    #[ManyToOne(targetEntity: UserCharacter::class, inversedBy: 'messages')]
    #[JoinColumn(name: 'character_id', referencedColumnName: 'id')]
    private UserCharacter $character;

    #[Groups(['message'])]
    #[Column(type: Types::STRING)]
    private string $content;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacter(): UserCharacter
    {
        return $this->character;
    }

    public function setCharacter(UserCharacter $character): void
    {
        $this->character = $character;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    #[Groups(['message'])]
    #[VirtualProperty]
    #[SerializedName('username')]
    public function getUsername(): string
    {
        return $this->character->getUsername();
    }
}