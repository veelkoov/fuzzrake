<?php

declare(strict_types=1);

namespace App\Twig\Utils;

class Counter
{
    private int $value = 0;

    public function next(): int
    {
        return $this->value++;
    }
}
