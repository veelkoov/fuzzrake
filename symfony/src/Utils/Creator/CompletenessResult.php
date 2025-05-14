<?php

declare(strict_types=1);

namespace App\Utils\Creator;

use App\Data\Definitions\Fields\Field;
use App\Utils\Creator\SmartAccessDecorator as Creator;

final class CompletenessResult
{
    private float $earned = 0.0;
    private float $total = 0.0;

    public function __construct(
        private readonly Creator $creator,
    ) {
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function result(): int
    {
        return 0.0 !== $this->total ? (int) round(100 * $this->earned / $this->total) : 0;
    }

    public function anyNotNull(float $weight, Field ...$fields): CompletenessResult
    {
        $this->total += $weight;

        foreach ($fields as $field) {
            $value = $this->creator->get($field);

            if (null !== $value) {
                $this->earned += $weight;
                break;
            }
        }

        return $this;
    }

    public function anyNotEmpty(float $weight, Field ...$fields): CompletenessResult
    {
        $this->total += $weight;

        foreach ($fields as $field) {
            if ($field->providedIn($this->creator)) {
                $this->earned += $weight;
                break;
            }
        }

        return $this;
    }

    public function add(float $weight, float $earned): void
    {
        $this->total += $weight;
        $this->earned += $earned;
    }
}
