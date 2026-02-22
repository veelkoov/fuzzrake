<?php

declare(strict_types=1);

namespace App\Entity;

use App\Data\Definitions\Fields\Validation;
use App\Repository\CreatorPrivateDataRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * NOTE: Private information given exclusively to the DB maintainer, must not be shared without creators' approval.
 *       Must never be dumped, nor committed.
 */
#[ORM\Entity(repositoryClass: CreatorPrivateDataRepository::class)]
#[ORM\Table(name: 'creators_private_data')]
class CreatorPrivateData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Creator::class, inversedBy: 'privateData')]
    #[ORM\JoinColumn(name: 'creator_id', unique: true, nullable: false)]
    private Creator $creator;

    #[NotBlank(message: 'Password is required.', groups: [Validation::GRP_CONTACT_AND_PASSWORD])]
    #[Length(min: 8, max: 255, minMessage: 'Passwords must now be 8 characters or longer. If you had a shorter one, please request a password change. Sorry for the inconvenience!', groups: [Validation::GRP_CONTACT_AND_PASSWORD])]
    #[ORM\Column(type: Types::TEXT)]
    private string $password = ''; // TODO: Remove/migrate constraints

    #[ORM\Column(name: 'email_address', type: Types::TEXT)]
    private string $emailAddress = ''; // TODO: Remove/migrate constraints

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreator(): Creator
    {
        return $this->creator;
    }

    public function setCreator(Creator $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }
}
