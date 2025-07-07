<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Email;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use TRegx\PhpUnit\DataProviders\DataProvider as TestDataProvider;

#[Small]
class EmailTest extends TestCase
{
    #[DataProvider('isValidDataProvider')]
    public function testIsValid(string $input, bool $isValid): void
    {
        self::assertSame($isValid, Email::isValid($input));
    }

    public static function isValidDataProvider(): TestDataProvider
    {
        return TestDataProvider::tuples(
            ['', false],
            ['@example', false],
            ['example:@example', false],
            ['example:@example.com', false],
            ['contact@example.com', true],
        );
    }

    #[DataProvider('obfuscateDataProvider')]
    public function testObfuscate(string $input, string $expected): void
    {
        self::assertSame($expected, Email::obfuscate($input));
    }

    public static function obfuscateDataProvider(): TestDataProvider
    {
        return TestDataProvider::tuples(
            ['@example.com', '@e*********m'],
            ['e@example.com', 'e@e*********m'],
            ['ex@example.com', 'e*@e*********m'],
            ['exa@example.com', 'e*a@e*********m'],
            ['exam@example.com', 'e**m@e*********m'],
        );
    }
}
