<?php

declare(strict_types=1);

namespace App\Tests\Utils\Data\Fixer;

use App\Utils\Data\Fixer\SinceFixer;
use PHPUnit\Framework\TestCase;

class SinceFixerTest extends TestCase
{
    public function testReplacement(): void
    {
        $fixer = new SinceFixer();

        self::assertEquals('2021-02', $fixer->fix('ignored', '2021-02-15'));
        self::assertEquals('9999-99', $fixer->fix('ignored', '9999-99-99'));
    }
}
