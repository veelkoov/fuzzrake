<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\FieldsList;
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

    public static function copy(mixed $source, SmartAccessDecorator $target, FieldsList $fields): self
    {
        $result = new self();
        $notCopiedYet = $fields->asArray();

        if (!is_array($source)) {
            $result->addError('Input data is not an array.');
        }

        foreach (array_keys($source) as $fieldName) {
            $field = Field::tryFrom($fieldName);

            if (null === $field) {
                $result->addError("Unknown field: '$fieldName'.");
                continue;
            }

            if (!$fields->has($field)) {
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

            unset($notCopiedYet[$fieldName]);

            try {
                $target->set($field, $value);
            } catch (TypeError) {
                $result->addError("Field '$fieldName' contained a value of unexpected type.");
            }
        }

        if ([] !== $notCopiedYet) {
            $result->addError('The following fields were not provided with a value: '.implode(', ', array_map(fn ($field) => "'$field->value'", $notCopiedYet)));
        }

        return $result;
    }
}
