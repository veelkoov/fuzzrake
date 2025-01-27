<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DateTime\UtcClock;
use App\Utils\Enforce;
use App\Utils\Json;
use Exception;
use UnexpectedValueException;

class JsonArtisanDataLoader
{
    public function __construct(
        private readonly string $subdir,
    ) {
    }

    final public const array FIELDS_NOT_IN_TEST_DATA = [ // Fields which are not loaded from JSON, they are not impacted by import
        Field::COMPLETENESS,
        Field::CS_LAST_CHECK,
        Field::CS_TRACKER_ISSUE,
        Field::OPEN_FOR,
        Field::CLOSED_FOR,
        Field::SAFE_DOES_NSFW,
        Field::SAFE_WORKS_WITH_MINORS,
    ];

    /**
     * @param Field[] $skippedFields
     *
     * @throws Exception
     */
    public function getArtisanData(string $fileName, array $skippedFields = self::FIELDS_NOT_IN_TEST_DATA): Artisan
    {
        $fileName = $this->subdir."/$fileName.json";

        /**
         * @var array<string, list<string>|string|bool|null> $data
         */
        $data = Json::readFile(Paths::getTestDataPath($fileName));

        $result = new Artisan();

        foreach (Fields::all() as $fieldName => $field) {
            if (in_array($field, $skippedFields)) {
                continue;
            }

            if (!array_key_exists($fieldName, $data)) {
                throw new UnexpectedValueException("'$fileName' misses '$fieldName' key");
            }

            $value = $data[$fieldName];

            if (Field::AGES === $field) {
                $value = Ages::get(Enforce::nString($value));
            } elseif (Field::CONTACT_ALLOWED === $field) {
                $value = ContactPermit::get(Enforce::nString($value));
            } elseif (null !== $value && in_array($field, [Field::DATE_ADDED, Field::DATE_UPDATED])) {
                $value = '/now/' === $value ? UtcClock::now() : UtcClock::at(Enforce::string($value));
            }

            if ($field->isList()) {
                if (!is_array($value)) {
                    throw new UnexpectedValueException("'$fileName' should be an array in '$fieldName' key");
                }
            }

            $result->set($field, $value);

            unset($data[$fieldName]);
        }

        if ([] !== $data) {
            $csKeys = implode(', ', array_keys($data));

            throw new UnexpectedValueException("'$fileName' contains unknown keys: $csKeys");
        }

        return $result;
    }
}
