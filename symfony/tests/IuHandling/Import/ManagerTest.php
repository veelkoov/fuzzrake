<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Import;

use App\IuHandling\Exception\ManagerConfigError;
use App\IuHandling\Import\Manager;
use App\Utils\Creator\SmartAccessDecorator as Creator;
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
        $manager->correctCreator($creator = Creator::new());

        self::assertSame('abcdef', $creator->getName());

        $manager = new Manager('set NAME |fedcba|');
        $manager->correctCreator($creator = Creator::new());

        self::assertSame('fedcba', $creator->getName());
    }

    public function testAcceptCommand(): void
    {
        $manager = new Manager('accept');

        self::assertTrue($manager->isAccepted());
    }

    public function testClearCommand(): void
    {
        $manager = new Manager('clear NOTES');

        $creator = Creator::new()->setNotes('will be removed');

        self::assertSame('will be removed', $creator->getNotes());
        $manager->correctCreator($creator);
        self::assertSame('', $creator->getNotes());
    }

    public function testSetCommand(): void
    {
        $manager = new Manager('set NOTES "will be added"');

        $creator = Creator::new();

        self::assertSame('', $creator->getNotes());
        $manager->correctCreator($creator);
        self::assertSame('will be added', $creator->getNotes());
    }

    public function testMatchCreatorIdCommand(): void
    {
        $manager = new Manager('accept');

        self::assertNull($manager->getMatchedCreatorId());

        $manager = new Manager('match-maker-id TEST001');

        self::assertSame('TEST001', $manager->getMatchedCreatorId());
    }

    public function testInvalidCommand(): void
    {
        try {
            new Manager("set NOTES 'asdf'\npancakes\naccept");

            self::fail();
        } catch (ManagerConfigError $exception) {
            self::assertSame("Unknown command: 'pancakes'", $exception->getMessage());
        }
    }

    public function testHandlingInvalidFieldName(): void
    {
        try {
            new Manager("set NOTE 'blargh'");

            self::fail();
        } catch (ManagerConfigError $exception) {
            self::assertSame("Unknown field: 'NOTE'", $exception->getMessage());
        }
    }
}
