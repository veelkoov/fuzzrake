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
    private ?ArtisanUrl $url = null;

    /**
     * @ORM\Column(name="last_success", type="datetime", nullable=true)
     * FIXME: Rename column to UTC https://github.com/veelkoov/fuzzrake/issues/109
     */
    private ?DateTimeInterface $lastSuccessUtc = null;

    /**
     * @ORM\Column(name="last_failure", type="datetime", nullable=true)
     * FIXME: Rename column to UTC https://github.com/veelkoov/fuzzrake/issues/109
     */
    private ?DateTimeInterface $lastFailureUtc = null;

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

    public function getLastSuccessUtc(): ?DateTimeInterface
    {
        return $this->lastSuccessUtc;
    }

    public function setLastSuccessUtc(?DateTime $lastSuccessUtc): self
    {
        $this->lastSuccessUtc = $lastSuccessUtc;

        return $this;
    }

    public function getLastFailureUtc(): ?DateTimeInterface
    {
        return $this->lastFailureUtc;
    }

    public function setLastFailureUtc(?DateTimeInterface $lastFailureUtc): self
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

    public function getLastRequest(): ?DateTimeInterface
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
