<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtisanCommissionsStatusRepository")
 * @ORM\Table(name="artisans_commissions_statues")
 *
 * NOTE: Ephemeral information, can be recreated by running update command. Table should not be committed, as that
 *       would generate too much noise in the repo history
 */
class ArtisanCommissionsStatus
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Artisan", inversedBy="commissionsStatus")
     * @ORM\JoinColumn(name="artisan_id", nullable=false)
     */
    private $artisan;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $status = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastChecked = null;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $reason = '';

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

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLastChecked(): ?DateTimeInterface
    {
        return $this->lastChecked;
    }

    public function setLastChecked(?DateTimeInterface $lastChecked): self
    {
        $this->lastChecked = $lastChecked;

        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): ArtisanCommissionsStatus
    {
        $this->reason = $reason;

        return $this;
    }
}
