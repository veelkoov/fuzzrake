<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Utils\Collections\StringToCreator;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\Enforce;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use UnexpectedValueException;

/**
 * @phpstan-type FieldValue list<string>|string|bool|null
 * @phpstan-type InData array<string, FieldValue>
 * @phpstan-type MidData array<string, InData>
 * @phpstan-type OutData array<string, MidData>
 * @phpstan-type TestDataContainer array{test_data: list<OutData>}
 */
class YamlCreatorsDataLoader
{
    private const array FIELDS_NOT_IN_TEST_DATA = [ // Fields that are not impacted by import
        Field::CS_LAST_CHECK,
        Field::CS_TRACKER_ISSUE,
        Field::OPEN_FOR,
        Field::CLOSED_FOR,
        Field::SAFE_DOES_NSFW,
        Field::SAFE_WORKS_WITH_MINORS,
    ];

    /** @var list<string> */
    final public array $aliases = ['max_to_max', 'new_max', 'min_to_max', 'new_min', 'just_id'];

    /** @var array<string, array<string, array<string, FieldValue>>> */
    private array $data = ['before' => [], 'update' => [], 'after' => []];

    public readonly StringToCreator $before;
    public readonly StringToCreator $update;
    public readonly StringToCreator $after;

    public function __construct(
        private readonly string $dataFilePath,
    ) {
        /**
         * @var TestDataContainer $data
         */
        $data = Yaml::parseFile($this->dataFilePath);

        foreach ($data['test_data'] as $outData) {
            foreach ($outData as $outKey => $midData) {
                foreach ($this->solvePluses($midData) as $midKey => $inData) {
                    foreach ($this->solvePluses($inData) as $inKey => $value) {
                        $this->ingest($outKey, $midKey, $inKey, $value);
                    }
                }
            }
        }

        $fieldsNotInIuForm = Fields::all()->getValues()->minusAll(Fields::inIuForm()->getValues())->getValuesArray();

        $this->before = StringToCreator::mapFrom($this->data['before'],
            fn (array $data, string $key) => [$key, self::toObject($data, self::FIELDS_NOT_IN_TEST_DATA)]);
        $this->update = StringToCreator::mapFrom($this->data['update'],
            fn (array $data, string $key) => [$key, self::toObject($data, $fieldsNotInIuForm)]);
        $this->after = StringToCreator::mapFrom($this->data['after'],
            fn (array $data, string $key) => [$key, self::toObject($data, self::FIELDS_NOT_IN_TEST_DATA)]);
    }

    /**
     * @template T
     *
     * @param array<string, T> $data
     *
     * @return array<string, T>
     */
    private function solvePluses(array $data): array
    {
        $result = [];

        foreach ($data as $keyOrKeys => $value) {
            $keys = explode('+', $keyOrKeys);

            if ($keys !== array_unique($keys)) {
                throw new InvalidArgumentException("Key '$keyOrKeys' contains duplicate items.");
            }

            foreach ($keys as $key) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param FieldValue $value
     */
    private function ingest(string $outKey, string $midKey, string $inKey, array|bool|string|null $value): void
    {
        if ($this->isTimeKey($outKey)) {
            $time = $outKey;

            if ($this->isAliasKey($midKey)) {
                $creatorAlias = $midKey;
                $fieldName = $inKey;
            } else {
                $creatorAlias = $inKey;
                $fieldName = $midKey;
            }
        } elseif ($this->isAliasKey($outKey)) {
            $creatorAlias = $outKey;

            if ($this->isTimeKey($midKey)) {
                $time = $midKey;
                $fieldName = $inKey;
            } else {
                $time = $inKey;
                $fieldName = $midKey;
            }
        } else {
            $fieldName = $outKey;

            if ($this->isTimeKey($midKey)) {
                $time = $midKey;
                $creatorAlias = $inKey;
            } else {
                $time = $inKey;
                $creatorAlias = $midKey;
            }
        }

        if (!Fields::all()->names()->contains($fieldName)) {
            throw new InvalidArgumentException("Invalid specification: '$outKey'/'$midKey'/'$inKey'.");
        }

        $this->setData($creatorAlias, $fieldName, $time, $value);
    }

    private function isTimeKey(string $outKey): bool
    {
        return array_key_exists($outKey, $this->data);
    }

    private function isAliasKey(string $midKey): bool
    {
        return arr_contains($this->aliases, $midKey);
    }

    /**
     * @param FieldValue $value
     */
    private function setData(string $creatorAlias, string $fieldName, string $time, array|bool|string|null $value): void
    {
        $this->data[$time] ??= [];
        $this->data[$time][$creatorAlias] ??= [];

        $target = &$this->data[$time][$creatorAlias];

        if (array_key_exists($time, $target)) {
            throw new InvalidArgumentException("Redefined value: '$time'/'$creatorAlias'/'$fieldName'.");
        }

        $target[$fieldName] = $value;
    }

    /**
     * @param array<string, FieldValue> $data
     * @param list<Field>               $skippedFields
     */
    private static function toObject(array $data, array $skippedFields): Creator
    {
        $result = new Creator();

        foreach (Fields::all() as $fieldName => $field) {
            if (arr_contains($skippedFields, $field)) {
                continue;
            }

            if (!array_key_exists($fieldName, $data)) {
                throw new UnexpectedValueException("Missing '$fieldName' data.");
            }

            $value = $data[$fieldName];

            if (Field::AGES === $field) {
                $value = Ages::get(Enforce::nString($value));
            } elseif (Field::CONTACT_ALLOWED === $field) {
                $value = ContactPermit::get(Enforce::nString($value));
            } elseif (null !== $value && in_array($field, [Field::DATE_ADDED, Field::DATE_UPDATED], true)) {
                try {
                    $value = '/now/' === $value ? UtcClock::now() : UtcClock::at(Enforce::string($value));
                } catch (DateTimeException $exception) {
                    throw new RuntimeException(previous: $exception);
                }
            }

            if ($field->isList()) {
                if (!is_array($value)) {
                    throw new UnexpectedValueException("'$fieldName' data should be an array.");
                }
            }

            $result->set($field, $value);

            unset($data[$fieldName]);
        }

        if ([] !== $data) {
            $csKeys = implode(', ', array_keys($data));

            throw new UnexpectedValueException("Extra data: $csKeys.");
        }

        return $result;
    }
}
