<?php

declare(strict_types=1);

namespace App\Utils\Arrays;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\FieldsList;
use App\Utils\Artisan\SmartAccessDecorator;
use App\Utils\Enforce;
use App\Utils\StringList;
use InvalidArgumentException;
use TypeError;

class SafeArrayRead
{
    /**
     * @var array<mixed>
     */
    private readonly array $source;

    /**
     * @var string[]
     */
    private array $errors = [];

    /**
     * @var Field[]
     */
    private array $notCopiedYet;

    public function __construct(
        mixed $source,
        private readonly SmartAccessDecorator $target,
        private readonly FieldsList $fields,
    ) {
        $this->notCopiedYet = $fields->asArray();

        if (!is_array($source)) {
            $this->addError('Input data is not an array.');
        } else {
            $this->source = $source;
            $this->copyFields();
        }

        if ([] !== $this->notCopiedYet) {
            $fieldsList = implode(', ', array_map(fn ($field) => "'$field->value'", $this->notCopiedYet));
            $this->addError("Missing fields: $fieldsList.");
        }
    }

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

    private function copyFields(): void
    {
        foreach (array_keys($this->source) as $fieldName) {
            $this->copyField((string) $fieldName);
        }
    }

    private function copyField(string $fieldName): void
    {
        $field = Field::tryFrom($fieldName);

        if (null === $field) {
            $this->addError("Unknown field: '$fieldName'.");

            return;
        }

        if (!$this->fields->has($field)) {
            return;
        }

        $value = $this->source[$fieldName];

        unset($this->notCopiedYet[$fieldName]);

        if ($field->isList()) {
            try {
                $value = Enforce::strList($value);
            } catch (InvalidArgumentException) {
                $this->addError("Field '$fieldName' was not an array of strings.");

                return;
            }

            $value = StringList::pack($value);
        }

        try {
            $this->target->set($field, $value);
        } catch (TypeError) {
            $this->addError("Field '$fieldName' contained a value of unexpected type.");
        }
    }
}
