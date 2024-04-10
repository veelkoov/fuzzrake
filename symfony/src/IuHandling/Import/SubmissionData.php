<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\IuHandling\SchemaFixer;
use App\Utils\DataInputException;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\Enforce;
use App\Utils\FieldReadInterface;
use App\Utils\Json;
use DateTimeImmutable;
use JsonException;
use Symfony\Component\Finder\SplFileInfo;

readonly class SubmissionData implements FieldReadInterface
{
    /**
     * @param array<string, psJsonFieldValue> $data
     */
    public function __construct(
        private DateTimeImmutable $timestamp,
        private string $id,
        private array $data,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function get(Field $field): mixed
    {
        $fieldName = $field->name;

        if (!array_key_exists($fieldName, $this->data)) {
            throw new DataInputException("Submission $this->id is missing $fieldName");
        }

        $value = $this->data[$fieldName];

        if ($field->isList() && !is_array($value)) {
            throw new DataInputException("Expected an array for $fieldName, got '$value' instead in $this->id");
        }

        if (Field::AGES === $field) {
            $value = Ages::get(Enforce::nString($value));
        }

        if (Field::CONTACT_ALLOWED === $field) {
            $value = ContactPermit::get(Enforce::nString($value));
        }

        return $value;
    }

    public function getString(Field $field): string
    {
        return Enforce::string($this->get($field));
    }

    public function getStringList(Field $field): array
    {
        return Enforce::strList($this->get($field));
    }

    public function hasData(Field $field): bool
    {
        return $field->providedIn($this);
    }

    public static function fromFile(SplFileInfo $source): self
    {
        $timestamp = self::getTimestampFromFilePath($source->getRelativePathname());
        $id = self::getIdFromFilePath($source->getRelativePathname());

        try {
            /**
             * @var array<string, psJsonFieldValue> $data
             */
            $data = Json::decode($source->getContents());
        } catch (JsonException $ex) {
            throw new DataInputException(previous: $ex);
        }

        return new self($timestamp, $id, (new SchemaFixer())->fix($data));
    }

    private static function getTimestampFromFilePath(string $filePath): DateTimeImmutable
    {
        $dateTimeStr = pattern('^(?:.*/)?(\d{4})/(\d{2})/(\d{2})/(\d{2}:\d{2}:\d{2})_\d{4}\.json$')
            ->replace($filePath)
            ->first()
            ->withReferences('$1-$2-$3 $4');

        try {
            return UtcClock::at($dateTimeStr);
        } catch (DateTimeException $exception) {
            throw new DataInputException("Couldn't parse the timestamp ('$dateTimeStr') out of the I/U submission file path: '$filePath'", previous: $exception);
        }
    }

    public static function getIdFromFilePath(string $filePath): string
    {
        return pattern('^(?:.*/)?(\d{4})/(\d{2})/(\d{2})/(\d{2}):(\d{2}):(\d{2})_(\d{4})\.json$')
            ->replace($filePath)
            ->first()
            ->withReferences('$1-$2-$3_$4$5$6_$7');
    }

    public static function getFilePathFromId(string $id): string
    {
        return pattern('^(\d{4})-(\d{2})-(\d{2})_(\d{2})(\d{2})(\d{2})_(\d{4})$')
            ->replace($id)
            ->first()
            ->withReferences('$1/$2/$3/$4:$5:$6_$7.json');
    }
}
