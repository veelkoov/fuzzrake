<?php

declare(strict_types=1);

namespace App\Utils;

class CompletenessCalc
{
    const CRUCIAL = 20;
    const IMPORTANT = 10;
    const AVERAGE = 5;
    const MINOR = 2;
    const TRIVIAL = 1;
    const INSIGNIFICANT = 0;

    private $earned = 0;
    private $total = 0;

    public function result(): ?int
    {
        return null !== $this->total ? (int) ($this->earned / $this->total * 100) : null;
    }

    public function anyNotNull(int $weight, ...$stuff): CompletenessCalc
    {
        $this->total += $weight;

        foreach ($stuff as $item) {
            if (null !== $item) {
                $this->earned += $weight;
                break;
            }
        }

        return $this;
    }

    public function anyNotEmpty(int $weight, ...$stuff): CompletenessCalc
    {
        $this->total += $weight;

        foreach ($stuff as $item) {
            if (null !== $item && '' !== $item) {
                $this->earned += $weight;
                break;
            }
        }

        return $this;
    }
}
