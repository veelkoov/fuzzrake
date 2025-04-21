<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CreatorUrlStateRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreatorUrlStateRepository::class)]
#[ORM\Table(name: 'creators_urls_states')]
class CreatorUrlState
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: CreatorUrl::class, inversedBy: 'state')]
    #[ORM\JoinColumn(name: 'creator_url_id', nullable: false)]
    private CreatorUrl $url;

    #[ORM\Column(name: 'last_success', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $lastSuccessUtc = null;

    #[ORM\Column(name: 'last_failure', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $lastFailureUtc = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $lastFailureCode = 0;

    #[ORM\Column(type: Types::TEXT)]
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

    public function getUrl(): CreatorUrl
    {
        return $this->url;
    }

    public function setUrl(CreatorUrl $url): CreatorUrlState
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
