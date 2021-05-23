<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtisanPrivateDataRepository")
 * @ORM\Table(name="artisans_private_data")
 *
 * NOTE: Private information given exclusively to the DB maintainer, must not be shared without makers' approval.
 *       Must never be dumped, nor committed.
 */
class ArtisanPrivateData
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Artisan", inversedBy="privateData")
     * @ORM\JoinColumn(name="artisan_id", nullable=false, unique=true)
     */
    private ?Artisan $artisan = null;

    /**
     * @ORM\Column(type="string", length=512)
     */
    private string $contactAddress = '';

    /**
     * @ORM\Column(type="string", length=255, name="passcode") # TODO: Revert
     * @Assert\NotBlank(groups={"iu_form"}, message="Password is required")
     * @Assert\Length(min=8, max=255, groups={"iu_form"}, minMessage="Passwords must now be 8 characters or longer. If you previously used a shorter one, please request a password change. Sorry for the inconvenience!")
     * grep-password-length
     */
    private string $password = '';

    /**
     * @ORM\Column(type="string", length=512)
     */
    private string $originalContactInfo = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArtisan(): ?Artisan
    {
        return $this->artisan;
    }

    public function setArtisan(Artisan $artisan): self
    {
        $this->artisan = $artisan;

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
}
