<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Email;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class EmailTest extends TestCase
{
    #[DataProvider('isValidDataProvider')]
    public function testIsValid(string $input, bool $isValid): void
    {
        self::assertSame($isValid, Email::isValid($input));
    }

    /**
     * @return list<array{string, bool}>
     */
    public static function isValidDataProvider(): array
    {
        return [
            ['', false],
            ['@example', false],
            ['example:@example', false],
            ['example:@example.com', false],
            ['contact@example.com', true],
        ];
    }

    #[DataProvider('obfuscateDataProvider')]
    public function testObfuscate(string $input, string $expected): void
    {
        self::assertSame($expected, Email::obfuscate($input));
    }

    /**
     * @return list<array{string, string}>
     */
    public static function obfuscateDataProvider(): array
    {
        return [
            ['@example.com', '@e*********m'],
            ['e@example.com', 'e@e*********m'],
            ['ex@example.com', 'e*@e*********m'],
            ['exa@example.com', 'e*a@e*********m'],
            ['exam@example.com', 'e**m@e*********m'],
        ];
    }
}
