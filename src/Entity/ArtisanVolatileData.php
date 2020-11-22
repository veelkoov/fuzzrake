<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
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
    private ?Artisan $artisan = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $status = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $lastCsUpdate = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $lastBpUpdate = null;

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

    public function getLastCsUpdate(): ?DateTimeInterface
    {
        return $this->lastCsUpdate;
    }

    public function setLastCsUpdate(?DateTimeInterface $lastCsUpdate): self
    {
        $this->lastCsUpdate = $lastCsUpdate;

        return $this;
    }

    public function getLastBpUpdate(): ?DateTimeInterface
    {
        return $this->lastBpUpdate;
    }

    public function setLastBpUpdate(?DateTimeInterface $lastBpUpdate): self
    {
        $this->lastBpUpdate = $lastBpUpdate;

        return $this;
    }
}
