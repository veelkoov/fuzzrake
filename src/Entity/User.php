<?php

declare(strict_types=1);

namespace App\Entity;

use App\Data\Definitions\ContactPermit;
use App\Repository\UserRepository;
use App\Utils\HasEmailGetter;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'users')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email. Forgotten password? You can find the reset option on the login form.')] // grep-code-email-already-registered
class User implements UserInterface, PasswordAuthenticatedUserInterface, HasEmailGetter, Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Email]
    #[ORM\Column(length: 180)]
    private string $email = ''; // grep-code-username-is-email

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private string $password = '';

    #[ORM\Column]
    private bool $isVerified = false;

    #[Assert\NotNull(message: 'You need to choose an option.')]
    #[ORM\Column(type: Types::TEXT, nullable: true, enumType: ContactPermit::class)]
    private ?ContactPermit $contactPermit = ContactPermit::CORRECTIONS;

    #[ORM\OneToOne(targetEntity: Creator::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Creator $creator = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Override]
    public function getEmail(): string // FIXME: No validation?
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    #[Override]
    public function getUserIdentifier(): string
    {
        if ('' === $this->email) {
            throw new LogicException(UserInterface::class.' object is not properly initialized.');
        }

        return $this->email;
    }

    /**
     * @see UserInterface
     */
    #[Override]
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // Guarantee every user at least has ROLE_USER

        if ($this->isVerified) {
            $roles[] = 'ROLE_VERIFIED';
        }

        return $roles;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = array_values(array_diff($roles, ['ROLE_USER', 'ROLE_VERIFIED'])); // Remove virtual roles

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    #[Override]
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getContactPermit(): ?ContactPermit
    {
        return $this->contactPermit;
    }

    public function setContactPermit(?ContactPermit $contactPermit): self
    {
        $this->contactPermit = $contactPermit;

        return $this;
    }

    public function getCreator(): ?Creator
    {
        return $this->creator;
    }

    public function setCreator(?Creator $creator, bool $force = false): self
    {
        if (!$force && null !== $this->creator && $creator !== $this->creator) {
            throw new LogicException('Trying to change already assigned creator.');
        }

        $this->creator = $creator;

        return $this;
    }

    #[Override]
    public function __toString()
    {
        return self::class."[$this->email]";
    }
}
