<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CreatorVolatileDataRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * NOTE: Ephemeral information, can be recreated by running update command. Table should not be committed, as that
 *       would generate too much noise in the repo history.
 */
#[ORM\Entity(repositoryClass: CreatorVolatileDataRepository::class)]
#[ORM\Table(name: 'creators_volatile_data')]
class CreatorVolatileData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Creator::class, inversedBy: 'volatileData')]
    #[ORM\JoinColumn(name: 'creator_id', nullable: false)]
    private Creator $creator;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $lastCsUpdate = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $csTrackerIssue = false;

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
