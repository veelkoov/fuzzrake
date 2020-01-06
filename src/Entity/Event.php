<?php

declare(strict_types=1);

namespace App\Entity;

use App\Utils\DateTime\DateTimeUtils;
use App\Utils\StrContext\StrContextInterface;
use App\Utils\StrContext\StrContextUtils;
use App\Utils\Tracking\AnalysisResult;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @ORM\Table(name="events")
 */
class Event
{
    const TYPE_CS_UPDATED = 'CS_UPDATED';
    const TYPE_CS_UPDATED_WITH_DETAILS = 'CS_UPDTD_DETLS';
    const TYPE_GENERIC = 'GENERIC';

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=4095)
     */
    private $description = '';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=16)
     */
    private $type = '';

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $oldStatus;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $newStatus = null;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=256)
     */
    private $artisanName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1024)
     */
    private $checkedUrl = '';

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="open_match")
     */
    private $openMatchRepr = '';

    /**
     * @var StrContextInterface
     */
    private $openMatch = null;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="closed_match")
     */
    private $closedMatchRepr = '';

    /**
     * @var StrContextInterface
     */
    private $closedMatch = null;

    public function __construct(string $checkedUrl = '', string $artisanName = '', ?bool $oldStatus = null, AnalysisResult $analysisResult = null)
    {
        $this->timestamp = DateTimeUtils::getNowUtc();
        $this->checkedUrl = $checkedUrl;
        $this->artisanName = $artisanName;
        $this->oldStatus = $oldStatus;

        if (null !== $analysisResult) {
            $this->type = self::TYPE_CS_UPDATED_WITH_DETAILS;
            $this->newStatus = $analysisResult->getStatus();
            $this->setClosedMatch($analysisResult->getClosedStrContext());
            $this->setOpenMatch($analysisResult->getOpenStrContext());
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTimestamp(): DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTimeInterface $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getOldStatus(): ?bool
    {
        return $this->oldStatus;
    }

    public function setOldStatus(?bool $oldStatus): void
    {
        $this->oldStatus = $oldStatus;
    }

    public function getNewStatus(): ?bool
    {
        return $this->newStatus;
    }

    public function setNewStatus(?bool $newStatus): void
    {
        $this->newStatus = $newStatus;
    }

    public function getArtisanName(): string
    {
        return $this->artisanName;
    }

    public function setArtisanName(string $artisanName): void
    {
        $this->artisanName = $artisanName;
    }

    public function getCheckedUrl(): string
    {
        return $this->checkedUrl;
    }

    public function setCheckedUrl(string $checkedUrl): void
    {
        $this->checkedUrl = $checkedUrl;
    }

    public function isLostTrack(): bool
    {
        return self::TYPE_CS_UPDATED === $this->type && null === $this->newStatus;
    }

    public function isChangedStatus(): bool
    {
        return in_array($this->type, [self::TYPE_CS_UPDATED, self::TYPE_CS_UPDATED_WITH_DETAILS]);
    }

    public function hasDetails(): bool
    {
        return self::TYPE_CS_UPDATED_WITH_DETAILS === $this->type;
    }

    public function getOpenMatch(): StrContextInterface
    {
        return $this->openMatch = $this->openMatch ?? StrContextUtils::fromString($this->openMatchRepr);
    }

    public function setOpenMatch(StrContextInterface $openMatch): void
    {
        $this->openMatch = $openMatch;
        $this->openMatchRepr = StrContextUtils::toStr($openMatch);
    }

    public function getClosedMatch(): StrContextInterface
    {
        return $this->closedMatch = $this->closedMatch ?? StrContextUtils::fromString($this->closedMatchRepr);
    }

    public function setClosedMatch(StrContextInterface $closedMatch): void
    {
        $this->closedMatch = $closedMatch;
        $this->closedMatchRepr = StrContextUtils::toStr($closedMatch);
    }
}
