<?php

declare(strict_types=1);

namespace App\Tests\Utils\Creator;

use App\Data\Definitions\Fields\Field;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class SmartAccessDecoratorTest extends TestCase
{
    /**
     * @throws DateTimeException
     */
    public function testEquals(): void
    {
        $subject1 = Creator::new()
            ->setIntro('Same intro')
            ->setCity('Oulu')
            ->setDateAdded(UtcClock::at('2022-09-23 11:46:11'))
            ->setDateUpdated(UtcClock::at('2022-09-24 12:34:56'))
            ->setOtherFeatures(['abcd', 'efgh', 'ijkl'])
            ->setOtherStyles(['qwer', 'asdf', 'zxcv'])
        ;

        $subject2 = Creator::new()
            ->setIntro('Same intro')
            ->setCity('Kuopio')
            ->setDateAdded(UtcClock::at('2022-09-23 11:46:11'))
            ->setDateUpdated(UtcClock::at('2022-09-24 11:22:33'))
            ->setOtherFeatures(['abcd', 'ijkl', 'efgh'])
            ->setOtherStyles(['qwer', 'asdf'])
        ;

        self::assertTrue($subject1->equals(Field::INTRO, $subject2));
        self::assertFalse($subject1->equals(Field::CITY, $subject2));
        self::assertTrue($subject1->equals(Field::DATE_ADDED, $subject2));
        self::assertFalse($subject1->equals(Field::DATE_UPDATED, $subject2));
        self::assertTrue($subject1->equals(Field::OTHER_FEATURES, $subject2));
        self::assertFalse($subject1->equals(Field::OTHER_STYLES, $subject2));
    }

    public function testSettingCreatorIdAddsToTheEmptyCollection(): void
    {
        $creator = new Creator();
        $creator->setCreatorId('TEST001');

        $this->validateCreatorIds($creator, 'TEST001', [], ['TEST001']);
    }

    public function testSettingCreatorIdAddsToTheCollection(): void
    {
        $creator = new Creator();
        $creator->setFormerCreatorIds(['TEST002', 'TEST003']);
        $creator->setCreatorId('TEST001');

        $this->validateCreatorIds($creator, 'TEST001', ['TEST002', 'TEST003'], ['TEST001', 'TEST002', 'TEST003']);
    }

    public function testSettingFormerCreatorIdsWorksWithNoCreatorIdSet(): void
    {
        $creator = new Creator();
        $creator->setFormerCreatorIds(['TEST003', 'TEST004']);

        $this->validateCreatorIds($creator, '', ['TEST003', 'TEST004'], ['TEST003', 'TEST004']);
    }

    public function testSettingFormerCreatorIdsRemovesObsoleteCreatorIdsLeavingAlreadyPresent(): void
    {
        $creator = new Creator();
        $creator->setFormerCreatorIds(['TEST005', 'TEST006']);

        $creator->setFormerCreatorIds(['TEST006', 'TEST007']);

        $this->validateCreatorIds($creator, '', ['TEST006', 'TEST007'], ['TEST006', 'TEST007']);
    }

    public function testSettingFormerCreatorIdsDoesntAffectCreatorId(): void
    {
        $creator = new Creator();
        $creator->setCreatorId('TEST003');
        $creator->setFormerCreatorIds(['TEST008', 'TEST009', 'TEST003']);

        $this->validateCreatorIds($creator, 'TEST003', ['TEST008', 'TEST009'], ['TEST003', 'TEST008', 'TEST009']);
    }

    /**
     * @param list<string> $formerCreatorIds
     * @param list<string> $allCreatorIds
     */
    private function validateCreatorIds(Creator $creator, string $creatorId, array $formerCreatorIds, array $allCreatorIds): void
    {
        self::assertSame($creatorId, $creator->getCreatorId());

        self::assertEquals($formerCreatorIds, $creator->getFormerCreatorIds());
        self::assertEquals($allCreatorIds, $creator->getAllCreatorIds());
    }
}
