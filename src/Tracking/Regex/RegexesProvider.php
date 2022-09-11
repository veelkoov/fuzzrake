<?php

declare(strict_types=1);

namespace App\Tracking\Regex;

interface RegexesProvider
{
    public function getRegexes(): Regexes;
}
