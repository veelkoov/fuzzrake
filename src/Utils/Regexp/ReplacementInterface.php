<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

interface ReplacementInterface
{
    public function do(string $input): string;
}
