<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\PackedStringList;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class PackedStringListTest extends TestCase
{
    /**
     * @param list<string> $nonsplittables
     * @param list<string> $expectedResult
     */
    #[DataProvider('splitDataProvider')]
    public function testSplit(string $input, string $separatorRegexp, array $nonsplittables, array $expectedResult): void
    {
        self::assertEquals($expectedResult, PackedStringList::split($input, $separatorRegexp, $nonsplittables));
    }

    /**
     * @return list<array{string, string, list<string>, list<string>}>
     */
    public static function splitDataProvider(): array
    {
        return [
            [
                'dog, wolf, fox, big and small cats, birds, bats, dragons, skullsuits...',
                "[\n,.]",
                ['skullsuits...'],
                ['dog', ' wolf', ' fox', ' big and small cats', ' birds', ' bats', ' dragons', ' skullsuits...'],
            ],
        ];
    }
}
