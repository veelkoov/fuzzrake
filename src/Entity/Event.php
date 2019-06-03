<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @ORM\Table(name="events")
 */
class Event
{
    const TYPE_LOST_CST = 'CST_LOST';
    const TYPE_CS_UPDATED = 'CS_UPDATED';
    const TYPE_GENERIC = 'GENERIC';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    /**
     * @ORM\Column(type="string", length=4095)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $oldStatus;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $newStatus;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $artisanName;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private $checkedUrl;

    /**
     * Event constructor.
     *
     * @param $description
     *
     * @throws \Exception
     */
    public function __construct(?string $description = null)
    {
        $this->timestamp = new DateTime('now', new DateTimeZone('UTC'));
        $this->description = $description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimestamp(): ?DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getOldStatus()
    {
        return $this->oldStatus;
    }

    /**
     * @param mixed $oldStatus
     */
    public function setOldStatus($oldStatus): void
    {
        $this->oldStatus = $oldStatus;
    }

    /**
     * @return mixed
     */
    public function getNewStatus()
    {
        return $this->newStatus;
    }

    /**
     * @param mixed $newStatus
     */
    public function setNewStatus($newStatus): void
    {
        $this->newStatus = $newStatus;
    }

    /**
     * @return mixed
     */
    public function getArtisanName()
    {
        return $this->artisanName;
    }

    /**
     * @param mixed $artisanName
     */
    public function setArtisanName($artisanName): void
    {
        $this->artisanName = $artisanName;
    }

    /**
     * @return mixed
     */
    public function getCheckedUrl()
    {
        return $this->checkedUrl;
    }

    /**
     * @param mixed $checkedUrl
     */
    public function setCheckedUrl($checkedUrl): void
    {
        $this->checkedUrl = $checkedUrl;
    }
}
