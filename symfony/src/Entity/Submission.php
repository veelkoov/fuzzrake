<?php

declare(strict_types=1);

namespace App\Entity;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Repository\SubmissionRepository;
use App\Utils\DataInputException;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\Enforce;
use App\Utils\FieldReadInterface;
use App\Utils\Json;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Override;
use RuntimeException;

#[ORM\Entity(repositoryClass: SubmissionRepository::class)]
#[ORM\Table(name: 'submissions')]
class Submission implements FieldReadInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, unique: true)]
    private string $strId = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $payload = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $directives = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $comment = '';

    /**
     * @var ?array<psJsonFieldValue>
     */
    private ?array $parsed = null;

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

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function setPayload(string $payload): Submission
    {
        $this->payload = $payload;

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

    public function getTimestamp(): DateTimeImmutable
    {
        // TODO: Use a column with value
        $dateTimeStr = pattern('^(\d{4}-\d{2}-\d{2})_(\d{2})(\d{2})(\d{2})_\d{4}$')
            ->replace($this->strId)
            ->first()
            ->withReferences('$1 $2:$3:$4');

        try {
            return UtcClock::at($dateTimeStr);
        } catch (DateTimeException $exception) {
            throw new DataInputException("Couldn't parse the timestamp from submission ID: '$this->strId'.", previous: $exception);
        }
    }

    /**
     * @return array<psJsonFieldValue>
     */
    private function getParsed(): array
    {
        try {
            return $this->parsed ??= Json::decode($this->payload); // @phpstan-ignore-line TODO: Affecting MX, future me - please forgive me.
        } catch (\JsonException $exception) {
            throw new DataInputException("Failed to parse submission as an array in '$this->strId'.", previous: $exception);
        }
    }

    #[Override]
    public function get(Field $field): mixed
    {
        $fieldName = $field->value;

        if (!array_key_exists($fieldName, $this->getParsed())) {
            throw new DataInputException("Submission $this->id is missing $fieldName");
        }

        $value = $this->getParsed()[$fieldName];

        if ($field->isList() && !is_array($value)) {
            throw new DataInputException("Expected an array for $fieldName, got '$value' instead in '$this->strId'.");
        }

        if (Field::AGES === $field) {
            $value = Ages::get(Enforce::nString($value));
        }

        if (Field::CONTACT_ALLOWED === $field) {
            $value = ContactPermit::get(Enforce::nString($value));
        }

        return $value;
    }

    #[Override]
    public function getString(Field $field): string
    {
        return Enforce::string($this->get($field));
    }

    #[Override]
    public function getStringList(Field $field): array
    {
        return Enforce::strList($this->get($field));
    }

    #[Override]
    public function hasData(Field $field): bool
    {
        return $field->providedIn($this);
    }
}
