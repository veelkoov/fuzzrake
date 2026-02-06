<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Twig\AdminExtensions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class AdminExtensionsTest extends TestCase
{
    #[DataProvider('linksAndSocialUrlsDataProvider')]
    public function testLinksAndSocialUrls(string $methodName, string $input, string $expectedOutput): void
    {
        $callable = [new AdminExtensions(), $methodName];
        self::assertIsCallable($callable);

        $result = call_user_func($callable, $input);
        self::assertSame($expectedOutput, $result);
    }

    /**
     * @return array<array{string, string, string}>
     */
    public static function linksAndSocialUrlsDataProvider(): array
    {
        return [
            [
                'linkUrls',
                'just plain text',
                'just plain text',
            ], [
                'linkUrls',
                'prefix http://getfursu.it/new suffix',
                'prefix <a href="http://getfursu.it/new" target="_blank">http://getfursu.it/new</a> suffix',
            ], [
                'linkUrls',
                'prefix http://getfursu.it/new middle https://getfursu.it/info suffix',
                'prefix <a href="http://getfursu.it/new" target="_blank">http://getfursu.it/new</a> middle <a href="https://getfursu.it/info" target="_blank">https://getfursu.it/info</a> suffix',
            ], [
                'blueskyAt',
                'https://bsky.app/profile/getfursuit.bsky.social',
                '@getfursuit.bsky.social',
            ], [
                'mastodonAt',
                'https://fursuits.online/@getfursuit',
                '@getfursuit@fursuits.online',
            ], [
                'tumblrAt',
                'https://www.tumblr.com/getfursuit',
                '@getfursuit _FIX_',
            ],
        ];
    }
}
