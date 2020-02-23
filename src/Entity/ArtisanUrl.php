<?php

declare(strict_types=1);

namespace App\Entity;

use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Web\Fetchable;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtisanUrlRepository")
 * @ORM\Table(name="artisans_urls")
 */
class ArtisanUrl implements Fetchable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Artisan", inversedBy="urls")
     * @ORM\JoinColumn(name="artisan_id", nullable=false)
     */
    private ?Artisan $artisan;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private string $type = '';

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private string $url = '';

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $lastSuccess = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $lastFailure = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $lastFailureCode = 0;

    /**
     * @ORM\Column(type="string", length=512)
     */
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

    public function getArtisan(): ?Artisan
    {
        return $this->artisan;
    }

    public function setArtisan(?Artisan $artisan): self
    {
        $this->artisan = $artisan;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getLastSuccess(): ?DateTime
    {
        return $this->lastSuccess;
    }

    public function setLastSuccess(DateTime $lastSuccess): self
    {
        $this->lastSuccess = $lastSuccess;

        return $this;
    }

    public function getLastFailure(): ?DateTime
    {
        return $this->lastFailure;
    }

    public function setLastFailure(DateTime $lastFailure): self
    {
        $this->lastFailure = $lastFailure;

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

    public function isDependency(): bool
    {
        return false;
    }

    public function recordSuccessfulFetch(): void
    {
        $this->lastSuccess = DateTimeUtils::getNowUtc();
    }

    public function recordFailedFetch(int $code, string $reason): void
    {
        $this->lastFailure = DateTimeUtils::getNowUtc();
        $this->lastFailureCode = $code;
        $this->lastFailureReason = $reason;
    }

    public function getOwnerName(): string
    {
        return $this->artisan->getName();
    }

    public function __toString()
    {
        return __CLASS__.":{$this->id}:{$this->url}";
    }
}
