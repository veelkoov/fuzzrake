<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\Utils\Artisan\Field;
use App\Utils\DataInputException;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\FieldReadInterface;
use App\Utils\Json;
use App\Utils\StringList;
use DateTimeInterface;
use JsonException;
use SplFileInfo;

class IuSubmission implements FieldReadInterface
{
    public function __construct(
        private DateTimeInterface $timestamp,
        private string $id,
        private array $data,
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

    /**
     * @throws DataInputException
     */
    public function get(Field $field)
    {
        $value = $this->data[$field->name()] ?? null;

        if (null === $value) {
            throw new DataInputException("Submission {$this->id} is missing {$field->name()}");
        }

        if ($field->isList() && !is_array($value)) {
            throw new DataInputException("Expected an array for {$field->name()}, got '$value' instead in {$this->id}");
        }

        return $field->isList() ? StringList::pack($value) : $value;
    }

    /**
     * @throws JsonException|DataInputException
     * @noinspection PhpPossiblePolymorphicInvocationInspection TODO: What
     */
    public static function fromFile(SplFileInfo $source): self
    {
        $timestamp = self::getTimestampFromFilePath($source->getRelativePathname());
        $id = self::getIdFromFilePath($source->getRelativePathname());
        $data = SchemaFixer::getInstance()->fix(Json::decode($source->getContents()), $timestamp);

        return new self($timestamp, $id, $data);
    }

    /**
     * @throws DataInputException
     */
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

    /**
     * @noinspection PhpDocRedundantThrowsInspection
     *
     * @throws DataInputException
     */
    private static function getIdFromFilePath(string $filePath): string
    {
        return pattern('^(?:.*/)?(\d{4})/(\d{2})/(\d{2})/(\d{2}):(\d{2}):(\d{2})_(\d{4})\.json$')
            ->replace($filePath)
            ->first()
            ->otherwise(function () {
                throw new DataInputException('Couldn\'t make an I/U submission ID out of the file path');
            })
            ->withReferences('$1-$2-$3_$4$5$6_$7');
    }
}
