<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Tracker\Regexes;
use App\Tracker\RegexesProvider;

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
