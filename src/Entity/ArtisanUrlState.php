<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtisanUrlStateRepository")
 * @ORM\Table(name="artisans_urls_states")
 */
class ArtisanUrlState
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ArtisanUrl", inversedBy="state")
     * @ORM\JoinColumn(name="artisan_url_id", nullable=false)
     */
    private ?ArtisanUrl $url;

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

    public function getUrl(): ?ArtisanUrl
    {
        return $this->url;
    }

    public function setUrl(?ArtisanUrl $url): ArtisanUrlState
    {
        $this->url = $url;

        return $this;
    }

    public function getLastSuccess(): ?DateTimeInterface
    {
        return $this->lastSuccess;
    }

    public function setLastSuccess(?DateTime $lastSuccess): self
    {
        $this->lastSuccess = $lastSuccess;

        return $this;
    }

    public function getLastFailure(): ?DateTimeInterface
    {
        return $this->lastFailure;
    }

    public function setLastFailure(?DateTimeInterface $lastFailure): self
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
}
