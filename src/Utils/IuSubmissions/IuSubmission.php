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
use TRegx\CleanRegex\Exception\SubjectNotMatchedException;
use TRegx\CleanRegex\Match\Details\Match;

class IuSubmission implements FieldReadInterface
{
    private DateTimeInterface $timestamp;
    private string $id;
    private string $fileName;
    private array $data;

    /**
     * @throws JsonException|DataInputException
     */

    /** @noinspection PhpPossiblePolymorphicInvocationInspection TODO: What */
    public function __construct(SplFileInfo $source)
    {
        $this->fileName = $source->getRelativePathname();
        $this->data = Json::decode($source->getContents());
        $this->setTimestamp($source->getRelativePathname());
        $this->setId($source->getRelativePathname());
    }

    public function getFileName(): string
    {
        return $this->fileName;
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
    private function setTimestamp(string $filePath): void
    {
        $dateTimeStr = pattern('^(?:.*/)?(\d{4})/(\d{2})/(\d{2})/(\d{2}:\d{2}:\d{2})_\d{4}\.json$')
            ->replace($filePath)->first()->withReferences('$1-$2-$3 $4');

        try {
            $this->timestamp = DateTimeUtils::getUtcAt($dateTimeStr);
        } catch (DateTimeException $e) {
            throw new DataInputException('Couldn\'t parse the timestamp out of the I/U submission file path', 0, $e);
        }
    }

    /**
     * @throws DataInputException
     */
    private function setId(string $filePath)
    {
        $id = pattern('^(?:.*/)?(\d{4})/(\d{2})/(\d{2})/(\d{2}):(\d{2}):(\d{2})_(\d{4})\.json$')
            ->replace($filePath)->first()->withReferences('$1-$2-$3_$4$5$6_$7');

        try {
            $this->id = pattern('^\d{4}-\d{2}-\d{2}_\d{2}\d{2}\d{2}_\d{4}$')->match($id)->first(function (Match $match): string {
                return $match->text();
            });
        } catch (SubjectNotMatchedException $e) {
            throw new DataInputException('Couldn\'t make an I/U submission ID out of the file path', 0, $e);
        }
    }

    /**
     * @throws DataInputException
     */
    public function get(Field $field)
    {
        $value = $this->data[$field->name()] ?? false;

        if (false === $value) {
            throw new DataInputException("Submission {$this->id} is missing {$field->name()}");
        }

        return $field->isList() ? StringList::pack($value) : $value;
    }
}
