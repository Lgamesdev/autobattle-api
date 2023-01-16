<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id;

    #[Column(type: 'string', length: 255)]
    private string $content;

    #[ManyToOne(targetEntity: UserCharacter::class, inversedBy: 'messages')]
    #[JoinColumn(nullable: false)]
    private UserCharacter $author;

    #[Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    private Channel $channel;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return UserCharacter
     */
    public function getAuthor(): UserCharacter
    {
        return $this->author;
    }

    /**
     * @param UserCharacter $author
     */
    public function setAuthor(UserCharacter $author): void
    {
        $this->author = $author;
    }

    /**
     * @return DateTime|\DateTimeInterface
     */
    public function getCreatedAt(): DateTime|\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime|\DateTimeInterface $createdAt
     */
    public function setCreatedAt(DateTime|\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return Channel
     */
    public function getChannel(): Channel
    {
        return $this->channel;
    }

    /**
     * @param Channel $channel
     */
    public function setChannel(Channel $channel): void
    {
        $this->channel = $channel;
    }


}