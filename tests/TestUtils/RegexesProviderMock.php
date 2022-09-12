<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Tracking\Regex\Regexes;
use App\Tracking\Regex\RegexesProvider;

class RegexesProviderMock implements RegexesProvider
{
    public function __construct(
        private readonly Regexes $regexes,
    ) {
    }

    public function getRegexes(): Regexes
    {
        return $this->regexes;
    }
}
