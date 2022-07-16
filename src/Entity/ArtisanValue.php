<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ArtisanValueRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArtisanValueRepository::class)
 * @ORM\Table(name="artisans_values")
 */
class ArtisanValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Artisan::class, inversedBy="values")
     * @ORM\JoinColumn(name="artisan_id", nullable=false)
     */
    private Artisan $artisan;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private string $fieldName = '';

    /**
     * @ORM\Column(type="string", length=4096)
     */
    private string $value = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): self
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
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
}
