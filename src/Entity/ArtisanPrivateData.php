<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Artisan", inversedBy="privateData", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $artisan;

    /**
     * @ORM\Column(type="string", length=512)
     */
    private $contactAddress = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $passcode = '';

    /**
     * @ORM\Column(type="string", length=512)
     */
    private $originalContactInfo = '';

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

    public function getPasscode(): string
    {
        return $this->passcode;
    }

    public function setPasscode(string $passcode): self
    {
        $this->passcode = $passcode;

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
