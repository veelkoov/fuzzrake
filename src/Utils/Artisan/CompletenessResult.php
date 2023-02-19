<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\Data\Definitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

final class CompletenessResult
{
    private float $earned = 0.0;
    private float $total = 0.0;

    public function __construct(
        private readonly Artisan $artisan,
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
            $value = $this->artisan->get($field);

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
            $value = $this->artisan->get($field);

            if (null !== $value && '' !== $value) {
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
