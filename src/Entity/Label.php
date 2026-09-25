<?php

declare(strict_types=1);

namespace App\Entity;

use App\Data\LabelType;
use App\Repository\LabelRepository;
use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LabelRepository::class)]
#[ORM\Table(name: 'labels')]
class Label
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public readonly DateTimeImmutable $addedAtUtc;

    #[ORM\Column(enumType: LabelType::class)]
    public LabelType $type;

    #[ORM\Column(type: Types::TEXT)]
    public string $value = '';

    #[ORM\Column(type: Types::TEXT)]
    public string $comment = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $activatedAtUtc = null;

    public bool $active {
        get => null !== $this->activatedAtUtc;
        set {
            $this->activatedAtUtc = $value ? $this->activatedAtUtc ?? UtcClock::now() : null;
        }
    }

    public function __construct(
        #[ORM\ManyToOne(inversedBy: 'labels')]
        #[ORM\JoinColumn(nullable: false)]
        public readonly Creator $creator,
    ) {
        $this->addedAtUtc = UtcClock::now();
    }

    public function setType(LabelType $type): self
    {
        $this->type = $type;

        return $this;
    }
}
