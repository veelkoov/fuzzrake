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
    final public const TYPE_DATA_UPDATED = 'DATA_UPDATED';
    final public const TYPE_CS_UPDATED = 'CS_UPDATED';
    final public const TYPE_GENERIC = 'GENERIC';

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
    private string $artisanName = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $checkedUrls = '';

    #[GreaterThanOrEqual(value: 0)]
    #[LessThan(value: 500)]
    #[ORM\Column(type: Types::INTEGER)]
    private int $newMakersCount = 0;

    #[GreaterThanOrEqual(value: 0)]
    #[LessThan(value: 500)]
    #[ORM\Column(type: Types::INTEGER)]
    private int $updatedMakersCount = 0;

    #[GreaterThanOrEqual(value: 0)]
    #[LessThan(value: 500)]
    #[ORM\Column(type: Types::INTEGER)]
    private int $reportedUpdatedMakersCount = 0;

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

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getArtisanName(): string
    {
        return $this->artisanName;
    }

    public function setArtisanName(string $artisanName): self
    {
        $this->artisanName = $artisanName;

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

    public function getNewMakersCount(): int
    {
        return $this->newMakersCount;
    }

    public function setNewMakersCount(int $newMakersCount): self
    {
        $this->newMakersCount = $newMakersCount;

        return $this;
    }

    public function getUpdatedMakersCount(): int
    {
        return $this->updatedMakersCount;
    }

    public function setUpdatedMakersCount(int $updatedMakersCount): self
    {
        $this->updatedMakersCount = $updatedMakersCount;

        return $this;
    }

    public function getReportedUpdatedMakersCount(): int
    {
        return $this->reportedUpdatedMakersCount;
    }

    public function setReportedUpdatedMakersCount(int $reportedUpdatedMakersCount): self
    {
        $this->reportedUpdatedMakersCount = $reportedUpdatedMakersCount;

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
        return self::TYPE_CS_UPDATED == $this->type;
    }

    public function isTypeDataUpdated(): bool
    {
        return self::TYPE_DATA_UPDATED === $this->type;
    }

    public function isEditable(): bool
    {
        return in_array($this->type, [self::TYPE_GENERIC, self::TYPE_DATA_UPDATED]);
    }
}
