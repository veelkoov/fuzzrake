<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\DataDefinitions\Ages;
use App\DataDefinitions\Fields\Field;
use App\Utils\DataInputException;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\FieldReadInterface;
use App\Utils\Json;
use App\Utils\Regexp\CountReceiver as Expect;
use App\Utils\StringList;
use DateTimeInterface;
use JsonException;
use Symfony\Component\Finder\SplFileInfo;

class IuSubmission implements FieldReadInterface
{
    public function __construct(
        private readonly DateTimeInterface $timestamp,
        private readonly string $id,
        private readonly array $data,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTimestamp(): DateTimeInterface
    {
        return $this->timestamp;
    }

    public function get(Field $field)
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
            $value = Ages::get($value);
        }

        return $field->isList() ? StringList::pack($value) : $value;
    }

    public static function fromFile(SplFileInfo $source): self
    {
        $timestamp = self::getTimestampFromFilePath($source->getRelativePathname());
        $id = self::getIdFromFilePath($source->getRelativePathname());
        try {
            $data = SchemaFixer::getInstance()->fix(Json::decode($source->getContents()));
        } catch (JsonException $ex) {
            throw new DataInputException(previous: $ex);
        }

        return new self($timestamp, $id, $data);
    }

    private static function getTimestampFromFilePath(string $filePath): DateTimeInterface
    {
        $dateTimeStr = pattern('^(?:.*/)?(\d{4})/(\d{2})/(\d{2})/(\d{2}:\d{2}:\d{2})_\d{4}\.json$')
            ->replace($filePath)->first()->withReferences('$1-$2-$3 $4');

        try {
            return DateTimeUtils::getUtcAt($dateTimeStr);
        } catch (DateTimeException $e) {
            throw new DataInputException('Couldn\'t parse the timestamp out of the I/U submission file path', 0, $e);
        }
    }

    private static function getIdFromFilePath(string $filePath): string
    {
        return pattern('^(?:.*/)?(\d{4})/(\d{2})/(\d{2})/(\d{2}):(\d{2}):(\d{2})_(\d{4})\.json$')
            ->replace($filePath)
            ->counting(Expect::once(DataInputException::class))
            ->withReferences('$1-$2-$3_$4$5$6_$7');
    }
}
