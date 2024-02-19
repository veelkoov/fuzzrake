<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Import;

use App\IuHandling\Exception\ManagerConfigError;
use App\IuHandling\Import\Manager;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class ManagerTest extends TestCase
{
    public function testBehaviorWithoutTrailingNewline(): void
    {
        $manager = new Manager('// Test comment without trailing \n');
        self::assertFalse($manager->isAccepted());

        $manager = new Manager('accept');
        self::assertTrue($manager->isAccepted());
    }

    public function testDetectingDelimiter(): void
    {
        $manager = new Manager('set NAME |abcdef|');
        $manager->correctArtisan($artisan = Artisan::new());

        self::assertEquals('abcdef', $artisan->getName());

        $manager = new Manager('set NAME |fedcba|');
        $manager->correctArtisan($artisan = Artisan::new());

        self::assertEquals('fedcba', $artisan->getName());
    }

    public function testAcceptCommand(): void
    {
        $manager = new Manager('accept');

        self::assertTrue($manager->isAccepted());
    }

    public function testClearCommand(): void
    {
        $manager = new Manager('clear NOTES');

        $artisan = Artisan::new()->setNotes('will be removed');

        self::assertEquals('will be removed', $artisan->getNotes());
        $manager->correctArtisan($artisan);
        self::assertEquals('', $artisan->getNotes());
    }

    public function testSetCommand(): void
    {
        $manager = new Manager('set NOTES "will be added"');

        $artisan = Artisan::new();

        self::assertEquals('', $artisan->getNotes());
        $manager->correctArtisan($artisan);
        self::assertEquals('will be added', $artisan->getNotes());
    }

    public function testMatchMakerIdCommand(): void
    {
        $manager = new Manager('accept');

        self::assertNull($manager->getMatchedMakerId());

        $manager = new Manager('match-maker-id MI12345');

        self::assertEquals('MI12345', $manager->getMatchedMakerId());
    }

    public function testInvalidCommand(): void
    {
        try {
            new Manager("set NOTES 'asdf'\npancakes\naccept");

            self::fail();
        } catch (ManagerConfigError $exception) {
            self::assertEquals("Unknown command: 'pancakes'", $exception->getMessage());
        }
    }
}
