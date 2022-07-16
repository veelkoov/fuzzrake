<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\DataDefinitions\Fields\Field;
use Stringable;

class ValueCorrection implements Stringable
{
    public function __construct(
        private readonly string $subject,
        private readonly Field $field,
        private readonly ?string $wrongValue,
        private readonly string $correctedValue,
    ) {
    }

    public function getField(): Field
    {
        return $this->field;
    }

    public function apply(string $value): string
    {
        if (null === $this->wrongValue) {
            return $this->correctedValue;
        } else {
            return $value === $this->wrongValue ? $this->correctedValue : $value;
        }
    }

    public function __toString(): string
    {
        return "$this->subject {$this->field->value} '$this->wrongValue' '$this->correctedValue'";
    }
}
