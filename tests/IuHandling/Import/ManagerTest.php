<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Import;

use App\IuHandling\Import\Manager;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    public function testCommentWithoutNewlineAtTheEndOfTheBuffer(): void
    {
        self::expectNotToPerformAssertions();

        new Manager('// Test comment without trailing \n');
    }

    public function testAcceptWithoutNewlineAtTheEndOfTheBuffer(): void
    {
        self::expectNotToPerformAssertions();

        new Manager('accept');
    }

    public function testDetectingDelimiter(): void
    {
        $manager = new Manager('replace NAME |abcdef| "fedcba"');

        $artisan = (new Artisan())->setMakerId('ABCDEFG')->setName('abcdef');

        $manager->correctArtisan($artisan);

        self::assertEquals('fedcba', $artisan->getName());
    }
}
