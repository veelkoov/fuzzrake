<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests\Filters;

use App\Filtering\DataRequests\Filters\OpenForFilter;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class OpenForFilterTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     *
     * @param string[] $searched
     */
    public function testMatches(string $openFor, string $commissionsUrls, bool $csTrackerIssue, array $searched, bool $matched): void
    {
        $subject = new OpenForFilter($searched);
        $artisan = Artisan::new()->setOpenFor($openFor)->setCommissionsUrls($commissionsUrls)->setCsTrackerIssue($csTrackerIssue);

        self::assertEquals($matched, $subject->matches($artisan));
    }

    /**
     * @return list<array{string, string, bool, string[], bool}>
     */
    public function matchesProvider(): array
    {
        return [
            ['', '', false, [], false],

            ['', '', false, ['-'], true],
            ['', 'Address1', false, ['-'], false],

            ['', 'Address1', true, ['!'], true],
            ['', 'Address1', false, ['!'], false],
            ['Item1', 'Address1', true, ['Item2', '!'], true],

            ['Item1', 'Address1', false, ['Item2', '!', '-'], false],

            ['Item1', 'Address1', true, ['Item1'], true],
            ['', 'Address1', false, ['Item1'], false],
            ["Item1\nItem2", 'Address1', false, ['Item1', 'Item2'], true],
            ['Item1', 'Address1', false, ['Item1', 'Item2'], true],
            ["Item1\nItem2", 'Address1', false, ['Item3'], false],
        ];
    }
}
