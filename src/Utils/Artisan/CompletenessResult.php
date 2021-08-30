<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\Utils\Artisan\SmartAccessDecorator as Artisan;

final class CompletenessResult
{
    private int $earned = 0;
    private int $total = 0;

    public function __construct(
        private Artisan $artisan,
    ) {
    }

    public function result(): int
    {
        return 0 !== $this->total ? (int) ($this->earned / $this->total * 100) : 0;
    }

    public function anyNotNull(int $weight, ...$fields): CompletenessResult
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

    public function anyNotEmpty(int $weight, ...$fields): CompletenessResult
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
}
