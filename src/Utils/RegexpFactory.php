<?php

declare(strict_types=1);

namespace App\Utils;

use SplObjectStorage;

class RegexpFactory
{
    const COMMON_REPLACEMENTS = [
        'commissions' => 'comm?iss?ions?',
        'open' => '(open(?!ing)|(?!not? |aren\'t |are not? )accepting|WE_CAN take)',
        'closed' => '(closed?|(not?|aren\'t|are not?) (accepting|seeking))',
        'fursuits' => 'fursuits?',
        '</div>' => ' ?</div> ?',
        '<div>' => ' ?<div( class="[^"]*")?> ?',
        '<p>' => ' ?<p( class="[^"]*")?> ?',
        '</p>' => ' ?</p> ?',
        'WE_CAN' => '(i|we) can(?! not? )',
        'WE_ARE' => '(we are|we\'re|i am|i\'m)',
        'MONTHS' => '(january|jan|february|feb|march|mar|april|apr|may|may|june|jun|july|jul|august|aug|september|sep|sept|october|oct|november|nov|december|dec)',
        'CURRENTLY' => '(currently|(right )?now|at (this|the) time)',
    ];

    public static function createSet(array $originals, array $variants = []): array
    {
        return array_map(function (string $original) use ($variants) {
            return self::create($original, $variants);
        }, $originals);
    }

    public static function create(string $original, array $variants = []): Regexp
    {
        $compiled = new SplObjectStorage();

        foreach ($variants as $variant) {
            $compiled[$variant] = self::compileVariant($original, $variant);
        }

        return new Regexp($original, $compiled);
    }

    private static function compileVariant(string $regexp, RegexpVariant $variant): string
    {
        $result = $regexp;

        foreach (array_merge($variant->getReplacements(), self::COMMON_REPLACEMENTS) as $needle => $replacement) {
            $result = str_replace($needle, $replacement, $result);
        }

        return "#$result#s";
    }
}
