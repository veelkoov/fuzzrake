<?php

declare(strict_types=1);

namespace App\Utils\DataInput;

use App\Utils\Artisan\Field;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\FieldReadInterface;
use App\Utils\Json;
use App\Utils\StringList;
use DateTimeInterface;
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
            $this->timestamp = DateTimeUtils::getNowUtc(); // FIXME
        } catch (DateTimeException $e) {
            throw new DataInputException("Failed parsing import row's date", 0, $e);
        }
    }

    /**
     * @throws DataInputException
     */
    private function setHash(array $rawNewData)
    {
        try {
            $this->hash = sha1(Json::encode($rawNewData));
        } catch (JsonException $e) {
            throw new DataInputException('Failed to calculate hash of the data row due to a JSON encoding error', 0, $e);
        }
    }

    public function get(Field $field)
    {
        $value = $this->rawInput[$field->name()];

        return $field->isList() ? StringList::pack($value) : $value;
    }
}
