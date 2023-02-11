<?php

declare(strict_types=1);

namespace App\Tests\Tracking\Regex;

use App\Tests\TestUtils\Cases\TestCase;
use App\Tracking\Regex\PlaceholdersResolver;

/**
 * @small
 */
class PlaceholdersResolverTest extends TestCase
{
    public function testResolvingPlaceholders(): void
    {
        $subject = new PlaceholdersResolver([
            ' AND ' => [' and '],
            'AAAA'  => ['aaaa'],
        ]);

        $testData = [
            'X AND X' => 'X( and )X',
            'XAND X' => 'XAND X',
            'X ANDX' => 'X ANDX',
            'XANDX' => 'XANDX',
            ' AND X' => '( and )X',
            'AND X' => 'AND X',
            'X AND ' => 'X( and )',
            'X AND' => 'X AND',
            ' AND' => ' AND',
            'AND ' => 'AND ',
            ' AND ' => '( and )',
            'AND' => 'AND',

            'X AAAA X' => 'X (aaaa) X',
            'XAAAA X' => 'XAAAA X',
            'X AAAAX' => 'X AAAAX',
            'XAAAAX' => 'XAAAAX',
            ' AAAA X' => ' (aaaa) X',
            'AAAA X' => '(aaaa) X',
            'X AAAA ' => 'X (aaaa) ',
            'X AAAA' => 'X (aaaa)',
            ' AAAA' => ' (aaaa)',
            'AAAA ' => '(aaaa) ',
            ' AAAA ' => ' (aaaa) ',
            'AAAA' => '(aaaa)',
        ];

        $expected = array_values($testData);
        $actual = array_keys($testData);
        $subject->resolve($actual);

        self::assertEquals($expected, $actual);
    }
}
