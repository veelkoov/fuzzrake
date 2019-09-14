<?php

declare(strict_types=1);

namespace App\Utils\Import;

use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use App\Utils\DateTimeException;
use App\Utils\DateTimeUtils;
use App\Utils\FieldReadInterface;
use App\Utils\JsonException;
use App\Utils\Utils;
use DateTimeInterface;

class RawImportItem implements FieldReadInterface
{
    /**
     * @var DateTimeInterface
     */
    private $timestamp;

    /**
     * @var array
     */
    private $rawInput;

    /**
     * @var string
     */
    private $hash;

    /**
     * @param array $rawInput
     *
     * @throws ImportException
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
     * @param array $rawNewData
     *
     * @throws ImportException
     */
    private function setTimestamp(array $rawNewData): void
    {
        try {
            $this->timestamp = DateTimeUtils::getUtcAt($rawNewData[Fields::uiFormIndex(Fields::TIMESTAMP)]);
        } catch (DateTimeException $e) {
            throw new ImportException("Failed parsing import row's date", 0, $e);
        }
    }

    private function setHash(array $rawNewData)
    {
        /* It looks like Google Forms changes timestamp's timezone,
         * so let's get rid of it for the sake of hash calculation */
        $rawNewData[Fields::uiFormIndex(Fields::TIMESTAMP)] = null;

        try {
            $this->hash = sha1(Utils::toJson($rawNewData));
        } catch (JsonException $e) {
            throw new RuntimeImportException('Failed to calculate hash of the data row due to a JSON encoding error', 0, $e);
        }
    }

    public function get(Field $field)
    {
        return $this->rawInput[$field->uiFormIndex()];
    }
}
