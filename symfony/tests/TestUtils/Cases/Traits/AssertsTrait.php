<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait AssertsTrait
{
    /**
     * Error output of the default makes result analysis difficult because the whole response is compared instead of just the code.
     *
     * @param AbstractBrowser<Request, Response> $client
     *
     * @see BrowserKitAssertionsTrait::assertResponseStatusCodeIs()
     */
    public static function assertResponseStatusCodeIs(AbstractBrowser $client, int $expectedCode): void
    {
        self::assertEquals($expectedCode, $client->getInternalResponse()->getStatusCode(), 'Unexpected HTTP response status code');
    }

    protected static function assertEqualsIgnoringWhitespace(string $expectedHtml, string $actualHtml): void
    {
        $pattern = pattern('\s+');

        $expectedHtml = trim($pattern->replace($expectedHtml)->with(' '));
        $actualHtml = trim($pattern->replace($actualHtml)->with(' '));

        self::assertEquals($expectedHtml, $actualHtml);
    }

    /**
     * @param mixed[] $expected
     * @param mixed[] $actual
     */
    protected function assertArrayItemsSameOrderIgnored(array $expected, array $actual, string $message = ''): void
    {
        sort($expected);
        sort($actual);

        self::assertEquals($expected, $actual, $message);
    }
}
