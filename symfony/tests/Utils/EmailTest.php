<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Email;
use PHPUnit\Framework\TestCase;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @small
 */
class EmailTest extends TestCase
{
    /**
     * @dataProvider isValidDataProvider
     */
    public function testIsValid(string $input, bool $isValid): void
    {
        self::assertSame($isValid, Email::isValid($input));
    }

    public function isValidDataProvider(): DataProvider
    {
        return DataProvider::tuples(
            ['', false],
            ['@example', false],
            ['example:@example', false],
            ['example:@example.com', false],
            ['contact@example.com', true],
        );
    }

    /**
     * @dataProvider obfuscateDataProvider
     */
    public function testObfuscate(string $input, string $expected): void
    {
        self::assertSame($expected, Email::obfuscate($input));
    }

    public function obfuscateDataProvider(): DataProvider
    {
        return DataProvider::tuples(
            ['@example.com', '@e*********m'],
            ['e@example.com', 'e@e*********m'],
            ['ex@example.com', 'e*@e*********m'],
            ['exa@example.com', 'e*a@e*********m'],
            ['exam@example.com', 'e**m@e*********m'],
        );
    }
}
