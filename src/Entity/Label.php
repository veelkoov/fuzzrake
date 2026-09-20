<?php

declare(strict_types=1);

namespace App\Entity;

use App\Data\LabelSubject;
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
    public private(set) DateTimeImmutable $addedAtUtc;

    public function __construct(
        #[ORM\ManyToOne(inversedBy: 'labels')]
        #[ORM\JoinColumn(nullable: false)]
        public private(set) Creator $creator,

        #[ORM\Column(enumType: LabelSubject::class)]
        public private(set) LabelSubject $subject,

        #[ORM\Column(enumType: LabelType::class)]
        public private(set) LabelType $type,

        #[ORM\Column(type: Types::TEXT)]
        public private(set) string $value = '',

        #[ORM\Column(type: Types::TEXT)]
        public private(set) string $comment = '',
    ) {
        $this->addedAtUtc = UtcClock::now();
    }
}
