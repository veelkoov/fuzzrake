<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CreatorIdRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreatorIdRepository::class)]
#[ORM\Table(name: 'creator_ids')]
class CreatorId
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Creator::class, inversedBy: 'creatorIds')]
    #[ORM\JoinColumn(name: 'owner_creator_id', nullable: false)]
    private Creator $creator;

    public function __construct(
        #[ORM\Column(type: Types::TEXT, unique: true)]
        private string $creatorId = '',
    ) {
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

    public function getCreatorId(): ?string
    {
        return $this->creatorId;
    }

    public function setCreatorId(string $creatorId): self
    {
        $this->creatorId = $creatorId;

        return $this;
    }
}
