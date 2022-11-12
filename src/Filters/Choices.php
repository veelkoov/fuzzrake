<?php

declare(strict_types=1);

namespace App\Filters;

class Choices
{
    /**
     * @param string[] $countries
     */
    public function __construct(
        public readonly array $countries,
    ) {
    }
}
