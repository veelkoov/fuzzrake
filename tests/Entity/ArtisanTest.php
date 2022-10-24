<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class ArtisanTest extends TestCase
{
    public function testSettingMakerIdAddsToTheEmptyCollection(): void
    {
        $artisan = new Artisan();
        $artisan->setMakerId('TSTMKID');

        $this->validateMakerIds($artisan, 'TSTMKID', '', 'TSTMKID');
    }

    public function testSettingMakerIdAddsToTheCollection(): void
    {
        $artisan = new Artisan();
        $artisan->setFormerMakerIds("FR1MKID\nFR2MKID");
        $artisan->setMakerId('TSTMKI2');

        $this->validateMakerIds($artisan, 'TSTMKI2', "FR1MKID\nFR2MKID", "FR1MKID\nFR2MKID\nTSTMKI2");
    }

    public function testSettingFormerMakerIdsWorksWithNoMakerIdSet(): void
    {
        $artisan = new Artisan();
        $artisan->setFormerMakerIds("FR3MKID\nFR4MKID");

        $this->validateMakerIds($artisan, '', "FR3MKID\nFR4MKID", "FR3MKID\nFR4MKID");
    }

    public function testSettingFormerMakerIdsRemovesObsoleteMakerIdsLeavingAlreadyPresent(): void
    {
        $artisan = new Artisan();
        $artisan->setFormerMakerIds("FR5MKID\nFR6MKID");

        $artisan->setFormerMakerIds("FR6MKID\nFR7MKID");

        $this->validateMakerIds($artisan, '', "FR6MKID\nFR7MKID", "FR6MKID\nFR7MKID");
    }

    public function testSettingFormerMakerIdsDoesntAffectMakerId(): void
    {
        $artisan = new Artisan();
        $artisan->setMakerId('TSTMKI3');
        $artisan->setFormerMakerIds("FR8MKID\nFR9MKID\nTSTMKI3");

        $this->validateMakerIds($artisan, 'TSTMKI3', "FR8MKID\nFR9MKID", "FR8MKID\nFR9MKID\nTSTMKI3");
    }

    private function validateMakerIds(Artisan $artisan, string $makerId, string $formerMakerIds, string $allMakerIds): void
    {
        self::assertEquals($makerId, $artisan->getMakerId());

        $exFormerMakerIds = explode("\n", $formerMakerIds);
        sort($exFormerMakerIds);
        $acFormerMakerIds = explode("\n", $artisan->getFormerMakerIds());
        sort($acFormerMakerIds);

        self::assertEquals($exFormerMakerIds, $acFormerMakerIds);

        $exAllMakerIds = explode("\n", $allMakerIds);
        sort($exAllMakerIds);
        $acAllMakerIds = $artisan->getAllMakerIdsArr();
        sort($acAllMakerIds);

        self::assertEquals($exAllMakerIds, $acAllMakerIds);
    }
}
