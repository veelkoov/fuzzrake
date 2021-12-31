<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\Fields;
use App\Utils\Artisan\SmartAccessDecorator;
use App\Utils\StringList;
use TypeError;

class SafeArrayRead
{
    /**
     * @var string[]
     */
    private array $errors = [];

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    private function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    public static function copy(mixed $source, SmartAccessDecorator $target): self
    {
        $result = new self();

        if (!is_array($source)) {
            $result->addError('Input data is not an array.');
        }

        foreach (array_keys($source) as $fieldName) {
            $field = Field::tryFrom($fieldName);

            if (null === $field) {
                $result->addError("Unknown field: '$fieldName'.");
                continue;
            }

            if (!$field->isInIuForm()) { // TODO: Somehow make czpcz
                continue;
            }

            $value = $source[$fieldName];

            if ($field->isList()) {
                if (!is_array($value) || !array_reduce($value, fn ($prev, $item) => $prev && is_string($item), true)) {
                    $result->addError("Field '$fieldName' was not an array of strings.");

                    return $result;
                }

                $value = StringList::pack($value);
            }

            try {
                $target->set($field, $value);
            } catch (TypeError) {
                $result->addError("Field '$fieldName' contained a value of unexpected type.");
            }

            // TODO: Should report missing?
        }

        return $result;
    }
}
