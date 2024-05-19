<?php

namespace App\Data;

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
        if ($field->isBoolean() && (null !== $value && !is_bool($value))) {
            throw new InvalidArgumentException("$field->value value must be a boolean.");
        }

        if ($field->isDate() && !(null === $value || $value instanceof DateTimeInterface)) {
            throw new InvalidArgumentException("$field->value value must be a DateTime.");
        }

        if ($field->isList()) {
            Enforce::strList($value);
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
                throw new InvalidArgumentException('Expected a boolean.');
            }
        } elseif ($field->isList()) {
            $value = PackedStringList::unpack($value);
        }

        return $value;
    }
}
