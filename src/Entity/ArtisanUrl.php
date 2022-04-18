<?php

declare(strict_types=1);

namespace App\Entity;

use App\Utils\DateTime\UtcClock;
use App\Utils\Web\Fetchable;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtisanUrlRepository")
 * @ORM\Table(name="artisans_urls")
 */
class ArtisanUrl implements Fetchable, Stringable
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
    private ?Artisan $artisan = null;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private string $type = '';

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private string $url = '';

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ArtisanUrlState", mappedBy="url", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private ?ArtisanUrlState $state = null;

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

    public function getState(): ArtisanUrlState
    {
        return $this->state ?? $this->state = (new ArtisanUrlState())->setUrl($this);
    }

    public function setState(ArtisanUrlState $state): self
    {
        $this->state = $state;

        if ($this !== $state->getUrl()) {
            $state->setUrl($this);
        }

        return $this;
    }

    public function isDependency(): bool
    {
        return false;
    }

    public function recordSuccessfulFetch(): void
    {
        $this->getState()
            ->setLastSuccessUtc(UtcClock::now());
    }

    public function recordFailedFetch(int $code, string $reason): void
    {
        $this->getState()
            ->setLastFailureUtc(UtcClock::now())
            ->setLastFailureCode($code)
            ->setLastFailureReason($reason);
    }

    public function resetFetchResults(): void
    {
        $this->getState()
            ->setLastFailureUtc(null)
            ->setLastSuccessUtc(null)
            ->setLastFailureReason('')
            ->setLastFailureCode(0);
    }

    public function getOwnerName(): string
    {
        return $this->artisan->getName();
    }

    public function __toString(): string
    {
        return self::class.":$this->id:$this->url";
    }
}
