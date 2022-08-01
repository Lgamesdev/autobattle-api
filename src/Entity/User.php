<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: 'username', message: 'This username is already used.')]
#[UniqueEntity(fields: 'email', message: 'This email is already used.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
	#[Id]
	#[GeneratedValue]
	#[Column(type: Types::INTEGER)]
	private ?int $id = null;

	#[Column(type: Types::STRING, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 4, max: 25)]
    private string $username;

	#[Column(type: Types::STRING, length: 180, unique: true)]
    #[Assert\Email]
    #[Assert\NotBlank]
	private string $email;

    #[Column(type: Types::STRING)]
    private string $password;

    #[OneToOne(mappedBy: 'user', targetEntity: PlayerCharacter::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private PlayerCharacter $character;

	public function __construct()
	{
        $this->character = new PlayerCharacter();
        $this->character->setUser($this);
	}

	function getId(): ?int
	{
		return $this->id;
	}

	function getUsername(): string
	{
		return $this->username;
	}

	function setUsername(string $username): self
	{
		$this->username = $username;
		return $this;
	}

	function getEmail(): string
	{
		return $this->email;
	}

	function setEmail(string $email): self
	{
		$this->email = $email;
		return $this;
	}

    function getPassword(): string
    {
        return $this->password;
    }

    function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    function getCharacter(): PlayerCharacter
    {
        return $this->character;
    }

    function setCharacter(PlayerCharacter $character): void
    {
        $this->character = $character;
    }

	function getRoles(): array
	{
		return ['ROLE_USER'];
	}

	function getUserIdentifier(): string
	{
		return $this->username;
	}

    function eraseCredentials()
    {
    }
}
