<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EventRepository;
use App\Utils\DateTime\UtcClock;
use App\Utils\PackedStringList;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'events')]
class Event
{
    final public const string TYPE_DATA_UPDATED = 'DATA_UPDATED';
    final public const string TYPE_CS_UPDATED = 'CS_UPDATED';
    final public const string TYPE_GENERIC = 'GENERIC';
    final public const string TYPE_CREATOR_ADDED = 'CREATOR_ADDED';
    final public const string TYPE_CREATOR_UPDATED = 'CREATOR_UPDATED';
    final public const array EDITABLE_TYPES = [
        self::TYPE_GENERIC,
        self::TYPE_DATA_UPDATED,
        self::TYPE_CREATOR_ADDED,
        self::TYPE_CREATOR_UPDATED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $timestamp;

    #[ORM\Column(type: Types::TEXT)]
    private string $description = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $type = self::TYPE_DATA_UPDATED;

    #[ORM\Column(type: Types::TEXT)]
    private string $noLongerOpenFor = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $nowOpenFor = '';

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $trackingIssues = false;

    #[ORM\Column(type: Types::TEXT)]
    private string $creatorName = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $creatorId = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $checkedUrls = '';

    #[GreaterThanOrEqual(value: 0)]
    #[LessThan(value: 500)]
    #[ORM\Column(type: Types::INTEGER)]
    private int $newCreatorsCount = 0;

    #[GreaterThanOrEqual(value: 0)]
    #[LessThan(value: 500)]
    #[ORM\Column(type: Types::INTEGER)]
    private int $updatedCreatorsCount = 0;

    #[GreaterThanOrEqual(value: 0)]
    #[LessThan(value: 500)]
    #[ORM\Column(type: Types::INTEGER)]
    private int $reportedUpdatedCreatorsCount = 0;

    #[Length(max: 256)]
    #[ORM\Column(type: Types::TEXT)]
    private string $gitCommits = '';

    public function __construct()
    {
        $this->timestamp = UtcClock::now();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTimeImmutable $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getNoLongerOpenFor(): string
    {
        return $this->noLongerOpenFor;
    }

    /**
     * @return string[]
     */
    public function getNoLongerOpenForArray(): array
    {
        return PackedStringList::unpack($this->noLongerOpenFor);
    }

    public function setNoLongerOpenFor(string $noLongerOpenFor): self
    {
        $this->noLongerOpenFor = $noLongerOpenFor;

        return $this;
    }

    public function getNowOpenFor(): string
    {
        return $this->nowOpenFor;
    }

    /**
     * @return string[]
     */
    public function getNowOpenForArray(): array
    {
        return PackedStringList::unpack($this->nowOpenFor);
    }

    public function setNowOpenFor(string $nowOpenFor): self
    {
        $this->nowOpenFor = $nowOpenFor;

        return $this;
    }

    public function getTrackingIssues(): bool
    {
        return $this->trackingIssues;
    }

    public function setTrackingIssues(bool $trackingIssues): self
    {
        $this->trackingIssues = $trackingIssues;

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

    public function getCreatorName(): string
    {
        return $this->creatorName;
    }

    public function setCreatorName(string $creatorName): self
    {
        $this->creatorName = $creatorName;

        return $this;
    }

    public function getCreatorId(): string
    {
        return $this->creatorId;
    }

    public function setCreatorId(string $creatorId): self
    {
        $this->creatorId = $creatorId;

        return $this;
    }

    public function getCheckedUrls(): string
    {
        return $this->checkedUrls;
    }

    /**
     * @return string[]
     */
    public function getCheckedUrlsArray(): array
    {
        return PackedStringList::unpack($this->checkedUrls);
    }

    public function setCheckedUrls(string $checkedUrls): self
    {
        $this->checkedUrls = $checkedUrls;

        return $this;
    }

    public function getNewCreatorsCount(): int
    {
        return $this->newCreatorsCount;
    }

    public function setNewCreatorsCount(int $newCreatorsCount): self
    {
        $this->newCreatorsCount = $newCreatorsCount;

        return $this;
    }

    public function getUpdatedCreatorsCount(): int
    {
        return $this->updatedCreatorsCount;
    }

    public function setUpdatedCreatorsCount(int $updatedCreatorsCount): self
    {
        $this->updatedCreatorsCount = $updatedCreatorsCount;

        return $this;
    }

    public function getReportedUpdatedCreatorsCount(): int
    {
        return $this->reportedUpdatedCreatorsCount;
    }

    public function setReportedUpdatedCreatorsCount(int $reportedUpdatedCreatorsCount): self
    {
        $this->reportedUpdatedCreatorsCount = $reportedUpdatedCreatorsCount;

        return $this;
    }

    public function getGitCommits(): string
    {
        return $this->gitCommits;
    }

    public function setGitCommits(string $gitCommits): self
    {
        $this->gitCommits = $gitCommits;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getGitCommitsArray(): array
    {
        return PackedStringList::unpack($this->gitCommits);
    }

    public function isTypeCsUpdated(): bool
    {
        return self::TYPE_CS_UPDATED === $this->type;
    }

    public function isTypeDataUpdated(): bool
    {
        return self::TYPE_DATA_UPDATED === $this->type;
    }

    public function isTypeCreatorAdded(): bool
    {
        return self::TYPE_CREATOR_ADDED === $this->type;
    }

    public function isTypeCreatorUpdated(): bool
    {
        return self::TYPE_CREATOR_UPDATED === $this->type;
    }

    public function isEditable(): bool
    {
        return arr_contains(self::EDITABLE_TYPES, $this->type);
    }
}
