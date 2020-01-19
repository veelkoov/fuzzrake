<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Regexp;

class StringFixer implements FixerInterface
{
    private const REPLACEMENTS = [
        '#’#'                 => "'",
        '#^-$#'               => '',
        '#^Rather not say$#i' => '',
        '#^n/a$#i'            => '',
        '#^n/a yet$#i'        => '',
        '#^-$#i'              => '',
        '#[ \t]{2,}#'         => ' ',
    ];

    public function fix(string $fieldName, string $subject): string
    {
        return trim(Regexp::replaceAll(self::REPLACEMENTS, $subject));
    }
}
