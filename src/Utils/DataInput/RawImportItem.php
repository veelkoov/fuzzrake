<?php

declare(strict_types=1);

namespace App\Utils\DataInput;

use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\FieldReadInterface;
use App\Utils\Json;
use DateTimeInterface;
use InvalidArgumentException;
use JsonException;

class RawImportItem implements FieldReadInterface
{
    private DateTimeInterface $timestamp;
    private array $rawInput;
    private string $hash;

    /**
     * @throws DataInputException
     */
    public function __construct(array $rawInput)
    {
        $this->rawInput = $rawInput;
        $this->setTimestamp($rawInput);
        $this->setHash($rawInput);
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getTimestamp(): DateTimeInterface
    {
        return $this->timestamp;
    }

    /**
     * @throws DataInputException
     */
    private function setTimestamp(array $rawNewData): void
    {
        try {
            $this->timestamp = DateTimeUtils::getUtcAt($rawNewData[Fields::uiFormIndex(Fields::TIMESTAMP)]);
        } catch (DateTimeException $e) {
            throw new DataInputException("Failed parsing import row's date", 0, $e);
        }
    }

    /**
     * @throws DataInputException
     */
    private function setHash(array $rawNewData)
    {
        /* It looks like Google Forms changes timestamp's timezone,
         * so let's get rid of it for the sake of hash calculation */
        $rawNewData[Fields::uiFormIndex(Fields::TIMESTAMP)] = null;

        try {
            $this->hash = sha1(Json::encode($rawNewData));
        } catch (JsonException $e) {
            throw new DataInputException('Failed to calculate hash of the data row due to a JSON encoding error', 0, $e);
        }
    }

    public function get(Field $field)
    {
        $uiFormIndex = $field->uiFormIndex();

        if (null === $uiFormIndex) {
            throw new InvalidArgumentException("{$field->name()} is not present in the IU form");
        }

        return $this->rawInput[$uiFormIndex];
    }
}
