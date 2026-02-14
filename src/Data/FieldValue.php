<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Utils\Enforce;
use App\Utils\PackedStringList;
use App\Utils\Parse;
use App\Utils\Traits\UtilityClass;
use DateTimeInterface;
use InvalidArgumentException;

final class FieldValue
{
    use UtilityClass;

    public static function validateType(Field $field, mixed $value): void
    {
        if ($field->isBoolean()) {
            if (null !== $value && !is_bool($value)) {
                throw new InvalidArgumentException("$field->value value must be a null or a boolean.");
            }
        } elseif ($field->isDate()) {
            if (null !== $value && !$value instanceof DateTimeInterface) {
                throw new InvalidArgumentException("$field->value value must be a null or date+time.");
            }
        } elseif ($field->isList()) {
            Enforce::strList($value);
        } elseif (Field::AGES === $field) {
            if (null !== $value && !$value instanceof Ages) {
                throw new InvalidArgumentException("$field->value value must be a null or its enum.");
            }
        } elseif (Field::CONTACT_ALLOWED === $field) {
            if (null !== $value && !$value instanceof ContactPermit) {
                throw new InvalidArgumentException("$field->value value must be a null or its enum.");
            }
        } else {
            if (!is_string($value)) {
                throw new InvalidArgumentException("$field->value value must be a string.");
            }
        }
    }

    /**
     * @return list<string>|string|bool
     */
    public static function fromString(Field $field, string $value): array|string|bool
    {
        if ($field->isBoolean()) {
            $value = Parse::nBool($value);

            if (null === $value) {
                throw new InvalidArgumentException("$field->value must be a string representation of a boolean.");
            }

            return $value;
        } elseif ($field->isList()) {
            return PackedStringList::unpack($value);
        } elseif ($field->isDate()) {
            throw new InvalidArgumentException("$field->value (date+time) conversion from string is not supported yet.");
        } elseif (Field::AGES === $field) {
            throw new InvalidArgumentException("$field->value (enum) conversion from string is not supported yet.");
        } elseif (Field::CONTACT_ALLOWED === $field) {
            throw new InvalidArgumentException("$field->value (enum) conversion from string is not supported yet.");
        } else {
            return $value;
        }
    }

    public static function isProvided(Field $field, mixed $value): bool
    {
        self::validateType($field, $value);

        return match (true) {
            $field->isDate()    => null !== $value,
            $field->isBoolean() => null !== $value,
            $field->isList()    => [] !== $value,

            Field::AGES === $field            => null !== $value,
            Field::CONTACT_ALLOWED === $field => null !== $value,

            default => '' !== $value,
        };
    }
}
