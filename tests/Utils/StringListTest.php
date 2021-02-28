<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\StringList;
use PHPUnit\Framework\TestCase;

class StringListTest extends TestCase
{
    /**
     * @dataProvider splitDataProvider
     *
     * @param string[] $nonsplittables
     * @param string[] $expectedResult
     */
    public function testSplit(string $input, string $separatorRegexp, array $nonsplittables, array $expectedResult): void
    {
        self::assertEquals($expectedResult, StringList::split($input, $separatorRegexp, $nonsplittables));
    }

    public function splitDataProvider(): array
    {
        return [
            [
                'dog, wolf, fox, big and small cats, birds, bats, dragons, skullsuits...', "[\n,.]", ['skullsuits...'],
                ['dog', ' wolf', ' fox', ' big and small cats', ' birds', ' bats', ' dragons', ' skullsuits...'],
            ],
        ];
    }
}
