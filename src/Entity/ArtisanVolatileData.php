<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtisanVolatileDataRepository")
 * @ORM\Table(name="artisans_volatile_data")
 *
 * NOTE: Ephemeral information, can be recreated by running update command. Table should not be committed, as that
 *       would generate too much noise in the repo history
 */
class ArtisanVolatileData
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Artisan", inversedBy="volatileData")
     * @ORM\JoinColumn(name="artisan_id", nullable=false)
     */
    private Artisan $artisan;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $lastCsUpdate = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $csTrackerIssue = false;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $lastBpUpdate = null; // TODO: Remove

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $bpTrackerIssue = false; // TODO: Remove

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArtisan(): Artisan
    {
        return $this->artisan;
    }

    public function setArtisan(Artisan $artisan): self
    {
        $this->artisan = $artisan;

        return $this;
    }

    public function getLastCsUpdate(): ?DateTimeImmutable
    {
        return $this->lastCsUpdate;
    }

    public function setLastCsUpdate(?DateTimeImmutable $lastCsUpdate): self
    {
        $this->lastCsUpdate = $lastCsUpdate;

        return $this;
    }

    public function getCsTrackerIssue(): bool
    {
        return $this->csTrackerIssue;
    }

    public function setCsTrackerIssue(bool $csTrackerIssue): self
    {
        $this->csTrackerIssue = $csTrackerIssue;

        return $this;
    }
}
