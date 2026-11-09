<?php

declare(strict_types=1);

namespace App\Entity;

use App\Data\LabelType;
use App\Repository\CreatorLabelRepository;
use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: CreatorLabelRepository::class)]
#[ORM\Table(name: 'creators_labels')]
#[ORM\Index(fields: ['type'])]
#[ORM\Index(fields: ['value'])]
#[ORM\UniqueConstraint(fields: ['type', 'value', 'creator'])]
#[UniqueEntity(['type', 'value', 'creator'], message: 'This creator already has this label assigned.')]
class CreatorLabel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public DateTimeImmutable $addedAtUtc;

    #[ORM\Column(enumType: LabelType::class)]
    public private(set) LabelType $type;

    #[ORM\Column(type: Types::TEXT)]
    public private(set) string $value = '';

    #[ORM\Column(type: Types::TEXT)]
    public private(set) string $comment = '';

    public function __construct(
        #[ORM\ManyToOne(inversedBy: 'labels')]
        #[ORM\JoinColumn(nullable: false)]
        public Creator $creator,
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

    #[Assert\Callback]
    public function validateValue(ExecutionContextInterface $context, mixed $payload): void
    {
        if (!arr_contains($this->type->getSubject()->getValidValues(), $this->value)) {
            $message = 'Invalid value. Allowed: "'.implode('", "', $this->type->getSubject()->getValidValues()).'"';
            $context->buildViolation($message)->atPath('value')->addViolation();
        }
    }
}
