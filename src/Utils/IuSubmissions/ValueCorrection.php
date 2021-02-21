<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\Utils\Artisan\Field;

class ValueCorrection
{
    private string $subject;
    private Field $field;
    private ?string $wrongValue;
    private string $correctedValue;

    public function __construct(string $subject, Field $field, ?string $wrongValue, string $correctedValue)
    {
        $this->subject = $subject;
        $this->field = $field;
        $this->wrongValue = $wrongValue;
        $this->correctedValue = $correctedValue;
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
