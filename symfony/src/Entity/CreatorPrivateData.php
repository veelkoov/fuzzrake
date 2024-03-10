<?php

declare(strict_types=1);

namespace App\Entity;

use App\Data\Definitions\Fields\Validation;
use App\Entity\Artisan as Creator;
use App\Repository\CreatorPrivateDataRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * NOTE: Private information given exclusively to the DB maintainer, must not be shared without makers' approval.
 *       Must never be dumped, nor committed.
 */
#[ORM\Entity(repositoryClass: CreatorPrivateDataRepository::class)]
#[ORM\Table(name: 'artisans_private_data')] // TODO: Rename
class CreatorPrivateData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'privateData', targetEntity: Creator::class)]
    #[ORM\JoinColumn(name: 'artisan_id', unique: true, nullable: false)] // TODO: Rename
    private Creator $creator;

    #[ORM\Column(type: Types::STRING, length: 512)]
    private string $contactAddress = '';

    #[NotBlank(message: 'Password is required.', groups: [Validation::GRP_CONTACT_AND_PASSWORD])]
    #[Length(min: 8, max: 255, minMessage: 'Passwords must now be 8 characters or longer. If you had a shorter one, please request a password change. Sorry for the inconvenience!', groups: [Validation::GRP_CONTACT_AND_PASSWORD])]
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $password = '';

    #[ORM\Column(type: Types::STRING, length: 512)]
    private string $originalContactInfo = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = '';

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

    public function getContactAddress(): string
    {
        return $this->contactAddress;
    }

    public function setContactAddress(string $contactAddress): self
    {
        $this->contactAddress = $contactAddress;

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

    public function getOriginalContactInfo(): string
    {
        return $this->originalContactInfo;
    }

    public function setOriginalContactInfo(string $originalContactInfo): self
    {
        $this->originalContactInfo = $originalContactInfo;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }
}
