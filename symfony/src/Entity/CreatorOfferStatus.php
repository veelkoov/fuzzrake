<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CreatorOfferStatusRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * NOTE: Ephemeral information, can be recreated by running update command. Table should not be committed, as that
 *       would generate too much noise in the repo history.
 */
#[ORM\Entity(repositoryClass: CreatorOfferStatusRepository::class)]
#[ORM\Table(name: 'artisans_commissions_statuses')] // TODO: Rename
class CreatorOfferStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Artisan::class, inversedBy: 'commissions')]
    #[ORM\JoinColumn(nullable: false)]
    private Artisan $artisan;

    #[ORM\Column(type: Types::STRING, length: 32)]
    private string $offer = '';

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isOpen = false;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOffer(): string
    {
        return $this->offer;
    }

    public function setOffer(string $offer): self
    {
        $this->offer = $offer;

        return $this;
    }

    public function getIsOpen(): bool
    {
        return $this->isOpen;
    }

    public function setIsOpen(bool $isOpen): self
    {
        $this->isOpen = $isOpen;

        return $this;
    }
}
