<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @ORM\Table(name="events")
 */
class Event
{
    const TYPE_CS_UPDATED = 'CS_UPDATED';
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
    private $type;

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
    private $newStatus;

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
    private $checkedUrl;

    /**
     * Event constructor.
     *
     * @param string    $checkedUrl
     * @param string    $artisanName
     * @param bool|null $oldStatus
     * @param bool|null $newStatus
     *
     * @throws Exception
     */
    public function __construct(string $checkedUrl, string $artisanName, ?bool $oldStatus, ?bool $newStatus)
    {
        $this->timestamp = new DateTime('now', new DateTimeZone('UTC'));
        $this->checkedUrl = $checkedUrl;
        $this->artisanName = $artisanName;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;

        $this->type = self::TYPE_CS_UPDATED;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return DateTimeInterface
     */
    public function getTimestamp(): DateTimeInterface
    {
        return $this->timestamp;
    }

    /**
     * @param DateTimeInterface $timestamp
     */
    public function setTimestamp(DateTimeInterface $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return bool|null
     */
    public function getOldStatus(): ?bool
    {
        return $this->oldStatus;
    }

    /**
     * @param bool|null $oldStatus
     */
    public function setOldStatus(?bool $oldStatus): void
    {
        $this->oldStatus = $oldStatus;
    }

    /**
     * @return bool|null
     */
    public function getNewStatus(): ?bool
    {
        return $this->newStatus;
    }

    /**
     * @param bool|null $newStatus
     */
    public function setNewStatus(?bool $newStatus): void
    {
        $this->newStatus = $newStatus;
    }

    /**
     * @return string
     */
    public function getArtisanName(): string
    {
        return $this->artisanName;
    }

    /**
     * @param string $artisanName
     */
    public function setArtisanName(string $artisanName): void
    {
        $this->artisanName = $artisanName;
    }

    /**
     * @return string
     */
    public function getCheckedUrl(): string
    {
        return $this->checkedUrl;
    }

    /**
     * @param string $checkedUrl
     */
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
        return self::TYPE_CS_UPDATED === $this->type;
    }
}
