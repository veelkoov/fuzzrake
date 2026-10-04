<?php

declare(strict_types=1);

namespace App\Entity;

use App\Data\LabelType;
use App\Repository\LabelRepository;
use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LabelRepository::class)]
#[ORM\Table(name: 'labels')]
#[ORM\Index(fields: ['type'])]
class Label
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public readonly DateTimeImmutable $addedAtUtc;

    #[ORM\Column(enumType: LabelType::class)]
    public private(set) LabelType $type;

    #[ORM\Column(type: Types::TEXT)]
    public private(set) string $value = '';

    #[ORM\Column(type: Types::TEXT)]
    public private(set) string $comment = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public private(set) ?DateTimeImmutable $activatedAtUtc = null;

    public bool $active {
        get => null !== $this->activatedAtUtc;
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

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function setActive(bool $active): self
    {
        $this->activatedAtUtc = $active ? $this->activatedAtUtc ?? UtcClock::now() : null;

        return $this;
    }

    // TODO
    //    #[Assert\Callback]
    //    public function validateValue(): void
    //    {
    //
    //    }
}
