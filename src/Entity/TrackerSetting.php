<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TrackerSettingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrackerSettingRepository::class)]
#[ORM\Table(name: 'tracker_settings')]
class TrackerSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'setting_group', type: Types::STRING, length: 255)]
    private string $group = '';

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $key = '';

    #[ORM\Column(type: Types::STRING, length: 2048)]
    private string $value = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

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
}
