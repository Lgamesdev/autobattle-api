<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\OneToMany;
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

    #[OneToOne(targetEntity: Wallet::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Wallet $wallet;

	#[Column(type: Types::STRING)]
	private string $password;

    #[OneToOne(targetEntity: Body::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Body $body;

    #[OneToOne(targetEntity: Inventory::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Inventory $inventory;

    #[Column(type: Types::BOOLEAN)]
    private bool $creationDone = false;

    #[Column(type: Types::BOOLEAN)]
    private bool $tutorialDone = false;

	public function __construct()
	{
        $this->body = new Body();
        $this->body->setUser($this);

        $this->wallet = new Wallet();
        $this->wallet->setUser($this);

        $this->inventory = new Inventory();
        $this->inventory->setUser($this);
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

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function currency(CurrencyType $type, int $amount) : void
    {
        $newCurrency = new Currency($type, $amount);
        $newCurrency->setWallet($this->wallet);
        $this->wallet->add($newCurrency);
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

    public function getBody(): Body
    {
        return $this->body;
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    public function isCreationDone(): bool
    {
        return $this->creationDone;
    }

    public function setCreationDone(bool $creationDone): void
    {
        $this->creationDone = $creationDone;
    }

    public function isTutorialDone(): bool
    {
        return $this->tutorialDone;
    }

    public function setTutorialDone(bool $tutorialDone): void
    {
        $this->tutorialDone = $tutorialDone;
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

    public function getUserInfos(): array
    {
        return [
            "level" => 1,
            "creationDone" => $this->isCreationDone(),
            "tutorialDone" => $this->isTutorialDone()
        ];
    }
}
