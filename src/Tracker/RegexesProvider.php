<?php

declare(strict_types=1);

namespace App\Tracker;

interface RegexesProvider
{
    public function getRegexes(): Regexes;
}
