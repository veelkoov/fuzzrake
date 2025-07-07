<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CreatorValueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreatorValueRepository::class)]
#[ORM\Table(name: 'creators_values')]
class CreatorValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Creator::class, inversedBy: 'values')]
    #[ORM\JoinColumn(name: 'creator_id', nullable: false)]
    private Creator $creator;

    #[ORM\Column(type: Types::TEXT)]
    private string $fieldName = '';

    #[ORM\Column(type: Types::TEXT)]
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
