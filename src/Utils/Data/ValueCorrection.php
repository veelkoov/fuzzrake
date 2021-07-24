<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\DataDefinitions\Field;
use Stringable;

class ValueCorrection implements Stringable
{
    public function __construct(
        private string $subject,
        private Field $field,
        private ?string $wrongValue,
        private string $correctedValue,
    ) {
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getField(): Field
    {
        return $this->field;
    }

    public function getWrongValue(): string
    {
        return $this->wrongValue;
    }

    public function getCorrectedValue(): string
    {
        return $this->correctedValue;
    }

    public function apply($value): string
    {
        if (null === $this->wrongValue) {
            return $this->correctedValue;
        } else {
            return $value === $this->wrongValue ? $this->correctedValue : $value;
        }
    }

    public function __toString(): string
    {
        return "{$this->subject} {$this->field} '{$this->wrongValue}' '{$this->correctedValue}'";
    }
}
