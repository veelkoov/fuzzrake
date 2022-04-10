<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TrackerSettingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrackerSettingRepository::class)
 * @ORM\Table(name="tracker_settings")
 */
class TrackerSetting
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255, name="setting_group")
     */
    private string $group = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $key = '';

    /**
     * @ORM\Column(type="string", length=2048)
     */
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
