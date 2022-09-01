<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ArtisanVolatileDataRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * NOTE: Ephemeral information, can be recreated by running update command. Table should not be committed, as that
 *       would generate too much noise in the repo history.
 */
#[ORM\Entity(repositoryClass: ArtisanVolatileDataRepository::class)]
#[ORM\Table(name: 'artisans_volatile_data')]
class ArtisanVolatileData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'volatileData', targetEntity: Artisan::class)]
    #[ORM\JoinColumn(name: 'artisan_id', nullable: false)]
    private Artisan $artisan;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $lastCsUpdate = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $csTrackerIssue = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $lastBpUpdate = null; // TODO: Remove

    #[ORM\Column(type: Types::BOOLEAN)]
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
