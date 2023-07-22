<?php

declare(strict_types=1);

namespace App\Tests\Tracking\Web;

use App\Tracking\Web\Url\UrlUtils;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class UrlUtilsTest extends TestCase
{
    /**
     * @dataProvider hostFromUrlDataProvider
     */
    public function testHostFromUrl(string $input, string $expected): void
    {
        self::assertEquals($expected, UrlUtils::hostFromUrl($input));
    }

    /**
     * @return array<array{string, string}>
     */
    public function hostFromUrlDataProvider(): array
    {
        return [
            ['https://www.getfursu.it/', 'getfursu.it'],
            ['http://www.getfursu.it/path', 'getfursu.it'],
            ['https://beta.getfursu.it/test', 'beta.getfursu.it'],
            ['httpsaaa!!!fff/', 'invalid_host'],
        ];
    }
}
