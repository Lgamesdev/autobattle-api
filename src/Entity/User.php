<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Entity]
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

	/**
	 * Collection of Currency
	 * @var Collection
	 */
	#[OneToMany(mappedBy: 'user', targetEntity: Currency::class, orphanRemoval: true)]
	private Collection $wallet;

	#[Column(type: Types::STRING)]
//    #[Assert\NotCompromisedPassword]
	private string $password;

	public function __construct()
	{
		$this->wallet = new ArrayCollection();
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

	function getWallet(): Collection
	{
		return $this->wallet;
	}

	public function addCurrency(Currency $currency): self
	{
		if (!$this->wallet->contains($currency)) {
			$this->wallet[] = $currency;
			$currency->setUser($this);
		}

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
