<?php

declare(strict_types=1);

namespace App\Entity;

use App\Data\Definitions\ContactPermit;
use App\Repository\UserRepository;
use App\Security\Role;
use App\Utils\HasEmailGetter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(type: Types::TEXT)]
    private string $nickname = '';

    #[Assert\NotBlank(message: 'Please enter your email.'), Assert\Length(max: 256), Assert\Email] // grep-code-email-constraints
    #[ORM\Column(type: Types::TEXT)]
    private string $email = ''; // grep-code-username-is-email

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: Types::TEXT)]
    private string $password = '';

    #[Assert\NotNull(message: 'You need to choose an option.')]
    #[ORM\Column(type: Types::TEXT, nullable: true, enumType: ContactPermit::class)]
    private ?ContactPermit $contactPermit = ContactPermit::CORRECTIONS;

    #[ORM\OneToOne(targetEntity: Creator::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Creator $creator = null;

    /**
     * @var Collection<int, UserRole>
     */
    #[ORM\OneToMany(targetEntity: UserRole::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $userRoles; // Named "userRoles" instead of simply "roles" because getRoles(): array is part of an interface

    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    #[Override]
    public function getEmail(): string
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
        $roles = $this->userRoles
            ->map(static fn (UserRole $role) => $role->getRole()->value)
            ->toArray();

        if (!arr_contains($roles, 'ROLE_VERIFIED') || arr_contains($roles, 'ROLE_LOCKED')) {
            $roles = []; // If we are not verified, or locked, you can't do anything
        }

        return $roles;
    }

    /**
     * @return Collection<int, UserRole>
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->hasRole($role)) {
            $this->userRoles->add(new UserRole($this, $role));
        }

        return $this;
    }

    public function hasRole(Role $role): bool
    {
        return $this->userRoles->exists(static fn (int $idx, UserRole $userRole) => $userRole->getRole() === $role);
    }

    public function removeRole(Role $role): self
    {
        $this->userRoles
            ->filter(static fn (UserRole $userRole) => $userRole->getRole() === $role)
            ->forAll(fn (int $idx, UserRole $userRole) => $this->userRoles->removeElement($userRole))
        ;

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

    public function setPassword(string $password): self
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

    public function allowsFeedback(): bool
    {
        return ContactPermit::FEEDBACK === $this->contactPermit;
    }

    #[Override]
    public function __toString()
    {
        return self::class."[$this->email]";
    }
}
