<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

trait AssertsTrait
{
    /**
     * Error output of the default makes result analysis difficult because the whole response is compared instead of just the code.
     *
     * @see BrowserKitAssertionsTrait::assertResponseStatusCodeSame()
     */
    public static function assertResponseStatusCodeIs(int $expectedCode): void
    {
        self::assertSame($expectedCode, self::$client->getInternalResponse()->getStatusCode(), 'Unexpected HTTP response status code');
    }

    protected static function assertEqualsIgnoringWhitespace(string $expectedHtml, string $actualHtml): void
    {
        $pattern = pattern('\s+');

        $expectedHtml = trim($pattern->replace($expectedHtml)->with(' '));
        $actualHtml = trim($pattern->replace($actualHtml)->with(' '));

        self::assertSame($expectedHtml, $actualHtml);
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
