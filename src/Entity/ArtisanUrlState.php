<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ArtisanUrlStateRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtisanUrlStateRepository::class)]
#[ORM\Table(name: 'artisans_urls_states')]
class ArtisanUrlState
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'state', targetEntity: ArtisanUrl::class)]
    #[ORM\JoinColumn(name: 'artisan_url_id', nullable: false)]
    private ArtisanUrl $url;

    #[ORM\Column(name: 'last_success', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastSuccessUtc = null;

    #[ORM\Column(name: 'last_failure', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastFailureUtc = null;

    #[ORM\Column(type: 'integer')]
    private int $lastFailureCode = 0;

    #[ORM\Column(type: 'string', length: 512)]
    private string $lastFailureReason = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUrl(): ArtisanUrl
    {
        return $this->url;
    }

    public function setUrl(ArtisanUrl $url): ArtisanUrlState
    {
        $this->url = $url;

        return $this;
    }

    public function getLastSuccessUtc(): ?DateTimeImmutable
    {
        return $this->lastSuccessUtc;
    }

    public function setLastSuccessUtc(?DateTimeImmutable $lastSuccessUtc): self
    {
        $this->lastSuccessUtc = $lastSuccessUtc;

        return $this;
    }

    public function getLastFailureUtc(): ?DateTimeImmutable
    {
        return $this->lastFailureUtc;
    }

    public function setLastFailureUtc(?DateTimeImmutable $lastFailureUtc): self
    {
        $this->lastFailureUtc = $lastFailureUtc;

        return $this;
    }

    public function getLastFailureCode(): int
    {
        return $this->lastFailureCode;
    }

    public function setLastFailureCode(int $lastFailureCode): self
    {
        $this->lastFailureCode = $lastFailureCode;

        return $this;
    }

    public function getLastFailureReason(): string
    {
        return $this->lastFailureReason;
    }

    public function setLastFailureReason(string $lastFailureReason): self
    {
        $this->lastFailureReason = $lastFailureReason;

        return $this;
    }

    public function getLastRequest(): ?DateTimeImmutable
    {
        $r1 = $this->lastFailureUtc;
        $r2 = $this->lastSuccessUtc;

        if (null === $r1) {
            return $r2;
        }

        if (null === $r2) {
            return $r1;
        }

        return max($r1, $r2);
    }
}
