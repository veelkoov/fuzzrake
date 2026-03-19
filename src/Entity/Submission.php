<?php

declare(strict_types=1);

namespace App\Entity;

use App\Data\Submission\Status;
use App\IuHandling\SubmissionDataReader;
use App\Repository\SubmissionRepository;
use App\Utils\DateTime\UtcClock;
use App\Utils\UnbelievableRuntimeException;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonException;
use Random\RandomException;

#[ORM\Entity(repositoryClass: SubmissionRepository::class)]
#[ORM\Table(name: 'submissions')]
class Submission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, unique: true)]
    private string $strId = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $submittedAtUtc;

    #[ORM\Column(type: Types::ENUM)]
    private Status $status = Status::NEW;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isUpdate;

    #[ORM\Column(type: Types::TEXT)]
    private string $payload = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $directives = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $comment = '';

    private ?SubmissionDataReader $reader = null;

    public function __construct(bool $isUpdate)
    {
        $this->isUpdate = $isUpdate;
        $this->submittedAtUtc = UtcClock::now();
        $this->strId = self::createStrId($this->submittedAtUtc);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStrId(): string
    {
        return $this->strId;
    }

    public function setStrId(string $strId): Submission
    {
        $this->strId = $strId;

        return $this;
    }

    public function getSubmittedAtUtc(): DateTimeImmutable
    {
        return $this->submittedAtUtc;
    }

    public function setSubmittedAtUtc(DateTimeImmutable $submittedAtUtc): Submission
    {
        $this->submittedAtUtc = $submittedAtUtc;

        return $this;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): Submission
    {
        $this->status = $status;

        return $this;
    }

    public function getIsUpdate(): ?bool
    {
        return $this->isUpdate;
    }

    public function setIsUpdate(bool $isUpdate): Submission
    {
        $this->isUpdate = $isUpdate;

        return $this;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function setPayload(string $payload): Submission
    {
        $this->payload = $payload;
        $this->reader = null;

        return $this;
    }

    public function getDirectives(): string
    {
        return $this->directives;
    }

    public function setDirectives(string $directives): Submission
    {
        $this->directives = $directives;

        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): Submission
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @throws JsonException
     */
    public function getReader(): SubmissionDataReader
    {
        return $this->reader ??= new SubmissionDataReader($this);
    }

    private static function createStrId(DateTimeImmutable $dateTimeImmutable): string
    {
        try {
            return $dateTimeImmutable->format('Y-m-d_His_').random_int(1000, 9999);
        } catch (RandomException $exception) {
            throw new UnbelievableRuntimeException($exception); // What is wrong with your OS, bro
        }
    }
}
