<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MakerIdRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MakerIdRepository::class)
 * @ORM\Table(name="maker_ids")
 */
class MakerId
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Artisan::class, inversedBy="makerIds")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Artisan $artisan = null;

    /**
     * @ORM\Column(type="string", length=7, unique=true, nullable=false)
     */
    private ?string $makerId;

    public function __construct(?string $makerId = null)
    {
        $this->makerId = $makerId;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMakerId(): ?string
    {
        return $this->makerId;
    }

    public function setMakerId(string $makerId): self
    {
        $this->makerId = $makerId;

        return $this;
    }
}
