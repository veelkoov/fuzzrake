<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CreatorSpecieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreatorSpecieRepository::class)]
#[ORM\Table(name: 'creators_species')]
class CreatorSpecie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(Specie::class)]
    #[ORM\JoinColumn('specie_id', nullable: false)]
    private Specie $specie;

    #[ORM\ManyToOne(Creator::class, inversedBy: 'species')]
    #[ORM\JoinColumn('creator_id', nullable: false)]
    private Creator $creator;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpecie(): Specie
    {
        return $this->specie;
    }

    public function setSpecie(Specie $specie): self
    {
        $this->specie = $specie;

        return $this;
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
}
