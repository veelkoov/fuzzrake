<?php

declare(strict_types=1);

namespace App\Utils\DataInput;

use App\Utils\Artisan\Field;
use App\Utils\Regexp\Regexp;
use InvalidArgumentException;

class ValueCorrection
{
    private string $makerId;
    private Field $field;
    private string $wrongValue;
    private string $correctedValue;

    public function __construct(string $makerId, Field $field, string $wrongValue, string $correctedValue)
    {
        $this->validateAndSetMakerId($makerId);
        $this->field = $field;
        $this->wrongValue = $wrongValue;
        $this->correctedValue = $correctedValue;
    }

    public function getMakerId(): string
    {
        return $this->makerId;
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

    public function apply($value)
    {
        return $value === $this->wrongValue ? $this->correctedValue : $value;
    }

    public function __toString(): string
    {
        return "{$this->makerId} {$this->field} {$this->wrongValue} {$this->correctedValue}";
    }

    private function validateAndSetMakerId(string $makerId): void
    {
        if (!Regexp::match('#^([A-Z0-9]{7}|\*)$#', $makerId)) {
            throw new InvalidArgumentException("Invalid maker ID: '$makerId'");
        }

        $this->makerId = $makerId;
    }
}
