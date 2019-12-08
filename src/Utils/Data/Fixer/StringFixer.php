<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Utils as Regexp;

class StringFixer implements FixerInterface
{
    public const REPLACEMENTS = [
        '#’#'                 => "'",
        '#^-$#'               => '',
        '#^Rather not say$#i' => '',
        '#^n/a$#i'            => '',
        '#^n/a yet$#i'        => '',
        '#^-$#i'              => '',
        '#[ \t]{2,}#'         => ' ',
    ];

    public function fix(string $subject): string
    {
        foreach (self::REPLACEMENTS as $pattern => $replacement) {
            $subject = Regexp::replace($pattern, $replacement, $subject);
        }

        return trim($subject);
    }
}
