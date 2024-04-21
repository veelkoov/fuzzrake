<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\KotlinDataRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'kotlin_data')]
#[ORM\Entity(repositoryClass: KotlinDataRepository::class)]
class KotlinData // TODO: Rename. Could serve a lot of purposes. Pre-computed data, settings, ...?
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $name = '';

    #[ORM\Column(name: 'json', type: Types::TEXT)] // TODO: Rename
    private string $data = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }
}
