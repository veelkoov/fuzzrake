<?php

declare(strict_types=1);

namespace App\Tests\Utils\Artisan;

use App\Data\Definitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
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

    public function testSettingMakerIdAddsToTheEmptyCollection(): void
    {
        $artisan = new Creator();
        $artisan->setMakerId('TSTMKID');

        $this->validateMakerIds($artisan, 'TSTMKID', [], ['TSTMKID']);
    }

    public function testSettingMakerIdAddsToTheCollection(): void
    {
        $artisan = new Creator();
        $artisan->setFormerMakerIds(['FR1MKID', 'FR2MKID']);
        $artisan->setMakerId('TSTMKI2');

        $this->validateMakerIds($artisan, 'TSTMKI2', ['FR1MKID', 'FR2MKID'], ['TSTMKI2', 'FR1MKID', 'FR2MKID']);
    }

    public function testSettingFormerMakerIdsWorksWithNoMakerIdSet(): void
    {
        $artisan = new Creator();
        $artisan->setFormerMakerIds(['FR3MKID', 'FR4MKID']);

        $this->validateMakerIds($artisan, '', ['FR3MKID', 'FR4MKID'], ['FR3MKID', 'FR4MKID']);
    }

    public function testSettingFormerMakerIdsRemovesObsoleteMakerIdsLeavingAlreadyPresent(): void
    {
        $artisan = new Creator();
        $artisan->setFormerMakerIds(['FR5MKID', 'FR6MKID']);

        $artisan->setFormerMakerIds(['FR6MKID', 'FR7MKID']);

        $this->validateMakerIds($artisan, '', ['FR6MKID', 'FR7MKID'], ['FR6MKID', 'FR7MKID']);
    }

    public function testSettingFormerMakerIdsDoesntAffectMakerId(): void
    {
        $artisan = new Creator();
        $artisan->setMakerId('TSTMKI3');
        $artisan->setFormerMakerIds(['FR8MKID', 'FR9MKID', 'TSTMKI3']);

        $this->validateMakerIds($artisan, 'TSTMKI3', ['FR8MKID', 'FR9MKID'], ['TSTMKI3', 'FR8MKID', 'FR9MKID']);
    }

    /**
     * @param list<string> $formerMakerIds
     * @param list<string> $allMakerIds
     */
    private function validateMakerIds(Creator $artisan, string $makerId, array $formerMakerIds, array $allMakerIds): void
    {
        self::assertEquals($makerId, $artisan->getMakerId());

        self::assertEquals($formerMakerIds, $artisan->getFormerMakerIds());
        self::assertEquals($allMakerIds, $artisan->getAllMakerIds());
    }
}
