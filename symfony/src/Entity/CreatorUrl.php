<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CreatorUrlRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Stringable;

#[ORM\Entity(repositoryClass: CreatorUrlRepository::class)]
#[ORM\Table(name: 'creators_urls')]
class CreatorUrl implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Creator::class, inversedBy: 'urls')]
    #[ORM\JoinColumn(name: 'creator_id', nullable: false)]
    private Creator $creator;

    #[ORM\Column(type: Types::TEXT)]
    private string $type = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $url = '';

    #[ORM\OneToOne(targetEntity: CreatorUrlState::class, mappedBy: 'url', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?CreatorUrlState $state = null;

    public function __clone()
    {
        if ($this->state) {
            $this->setState(clone $this->state);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getState(): CreatorUrlState
    {
        return $this->state ?? $this->state = (new CreatorUrlState())->setUrl($this);
    }

    public function setState(CreatorUrlState $state): self
    {
        $this->state = $state;

        if ($this !== $state->getUrl()) {
            $state->setUrl($this);
        }

        return $this;
    }

    public function resetFetchResults(): void
    {
        $this->getState()
            ->setLastFailureUtc(null)
            ->setLastSuccessUtc(null)
            ->setLastFailureReason('')
            ->setLastFailureCode(0);
    }

    #[Override]
    public function __toString(): string
    {
        return self::class.":$this->id:$this->url";
    }
}
