<?php

declare(strict_types=1);

namespace App\Entity;

use App\Utils\DateTime\DateTimeUtils;
use App\Utils\StringList;
use App\Utils\Tracking\AnalysisResult;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @ORM\Table(name="events")
 */
class Event
{
    public const TYPE_DATA_UPDATED = 'DATA_UPDATED';
    public const TYPE_CS_UPDATED = 'CS_UPDATED';
    public const TYPE_GENERIC = 'GENERIC';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $timestamp;

    /**
     * @ORM\Column(type="string", length=4095)
     */
    private string $description = '';

    /**
     * @ORM\Column(type="string", length=16)
     */
    private string $type = self::TYPE_DATA_UPDATED;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $oldStatus;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $newStatus;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $artisanName;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private string $checkedUrl;

    /**
     * @Assert\GreaterThanOrEqual(value="0")
     * @Assert\LessThan(value="500")
     * @ORM\Column(type="integer")
     */
    private int $newMakersCount = 0;

    /**
     * @Assert\GreaterThanOrEqual(value="0")
     * @Assert\LessThan(value="500")
     * @ORM\Column(type="integer")
     */
    private int $updatedMakersCount = 0;

    /**
     * @Assert\GreaterThanOrEqual(value="0")
     * @Assert\LessThan(value="500")
     * @ORM\Column(type="integer")
     */
    private int $reportedUpdatedMakersCount = 0;

    /**
     * @Assert\Length(max="256")
     * @ORM\Column(type="string", length=256)
     */
    private string $gitCommits = '';

    public function __construct(string $checkedUrl = '', string $artisanName = '', ?bool $oldStatus = null, AnalysisResult $analysisResult = null)
    {
        $this->timestamp = DateTimeUtils::getNowUtc();
        $this->checkedUrl = $checkedUrl;
        $this->artisanName = $artisanName;
        $this->oldStatus = $oldStatus;

        if (null !== $analysisResult) {
            $this->type = self::TYPE_CS_UPDATED;
            $this->newStatus = $analysisResult->getStatus();
        }
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

    public function getTimestamp(): DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTimeInterface $timestamp): self
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOldStatus(): ?bool
    {
        return $this->oldStatus;
    }

    public function setOldStatus(?bool $oldStatus): self
    {
        $this->oldStatus = $oldStatus;

        return $this;
    }

    public function getNewStatus(): ?bool
    {
        return $this->newStatus;
    }

    public function setNewStatus(?bool $newStatus): self
    {
        $this->newStatus = $newStatus;

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

    public function getCheckedUrl(): string
    {
        return $this->checkedUrl;
    }

    public function setCheckedUrl(string $checkedUrl): self
    {
        $this->checkedUrl = $checkedUrl;

        return $this;
    }

    public function getNewMakersCount(): int
    {
        return $this->newMakersCount;
    }

    public function setNewMakersCount(int $newMakersCount): Event
    {
        $this->newMakersCount = $newMakersCount;

        return $this;
    }

    public function getUpdatedMakersCount(): int
    {
        return $this->updatedMakersCount;
    }

    public function setUpdatedMakersCount(int $updatedMakersCount): Event
    {
        $this->updatedMakersCount = $updatedMakersCount;

        return $this;
    }

    public function getReportedUpdatedMakersCount(): int
    {
        return $this->reportedUpdatedMakersCount;
    }

    public function setReportedUpdatedMakersCount(int $reportedUpdatedMakersCount): Event
    {
        $this->reportedUpdatedMakersCount = $reportedUpdatedMakersCount;

        return $this;
    }

    public function getGitCommits(): string
    {
        return $this->gitCommits;
    }

    public function setGitCommits(string $gitCommits): Event
    {
        $this->gitCommits = $gitCommits;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getGitCommitsArray(): array
    {
        return StringList::unpack($this->gitCommits);
    }

    public function isLostTrack(): bool
    {
        return $this->isChangedStatus() && null === $this->newStatus;
    }

    public function isChangedStatus(): bool
    {
        return self::TYPE_CS_UPDATED == $this->type;
    }

    public function isUpdates(): bool
    {
        return self::TYPE_DATA_UPDATED === $this->type;
    }

    public function isEditable(): bool
    {
        return in_array($this->type, [self::TYPE_GENERIC, self::TYPE_DATA_UPDATED]);
    }
}
