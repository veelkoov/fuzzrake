<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web;

use App\Utils\Web\UrlUtils;
use PHPUnit\Framework\TestCase;

class UrlUtilsTest extends TestCase
{
    /**
     * @dataProvider hostFromUrlDataProvider
     */
    public function testHostFromUrl(string $input, string $expected): void
    {
        self::assertEquals($expected, UrlUtils::hostFromUrl($input));
    }

    public function hostFromUrlDataProvider(): array
    {
        return [
            ['https://www.getfursu.it/', 'getfursu.it'],
            ['https://beta.getfursu.it/test', 'beta.getfursu.it'],
            ['httpsaaa!!!fff/', 'invalid_host'],
        ];
    }

    /**
     * @dataProvider safeFileNameFromUrlDataProvider
     */
    public function testSafeFileNameFromUrl(string $input, string $expected): void
    {
        self::assertEquals($expected, UrlUtils::safeFileNameFromUrl($input));
    }

    public function safeFileNameFromUrlDataProvider(): array
    {
        return [
            ['https://getfursu.it/data_updates.html#anchor', 'getfursu.it_data_updates.html'],
            ['!@&$asdf$%&^asdf!@&$', 'asdf_asdf'],
        ];
    }
}
