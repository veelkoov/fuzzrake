<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;

/**
 * @medium
 */
class PasswordHandlingTest extends AbstractTestWithEM
{
    use IuFormTrait;

    public function testNewMakerPasswordIsHashed(): void
    {
        $this->client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($this->client);

        $form = $this->client->getCrawler()->selectButton('Submit')->form([
            'iu_form[makerId]' => 'TESTMID',
            'iu_form[name]' => 'Test name',
            'iu_form[country]' => 'Test country',
            'iu_form[ages]' => 'MIXED',
            'iu_form[nsfwWebsite]' => 'NO',
            'iu_form[nsfwSocial]' => 'NO',
            'iu_form[worksWithMinors]' => 'NO',
            'iu_form[contactAllowed]' => 'NO',
            'iu_form[password]' => 'some-password',
        ]);
        $this::submitValid($this->client, $form);

        self::assertIuSubmittedCorrectPassword();

        self::performImport($this->client, true, 1);
        self::flushAndClear();

        $artisan = self::findArtisanByMakerId('TESTMID');
        self::assertTrue(password_verify('some-password', $artisan->getPassword())); // Fails on plaintext
    }

    public function testMakerUpdatedWithCorrectPasswordHasUnchangedHash(): void
    {
        $artisan = self::getArtisan(
            name: 'Old name',
            makerId: 'MAKERID',
            password: 'known-password',
            contactAllowed: ContactPermit::NO,
            ages: Ages::MIXED,
            nsfwWebsite: false,
            nsfwSocial: false,
            worksWithMinors: true,
        );
        self::persistAndFlush($artisan);
        $oldHash = $artisan->getPassword();
        unset($artisan);

        $this->client->request('GET', '/iu_form/start/MAKERID');
        self::skipRulesAndCaptcha($this->client);

        $form = $this->client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]'     => 'New name',
            'iu_form[password]' => 'known-password',
        ]);
        $this::submitValid($this->client, $form);

        self::assertIuSubmittedCorrectPassword();

        self::performImport($this->client, true, 1);
        self::flushAndClear();

        $artisan = self::findArtisanByMakerId('MAKERID');
        self::assertEquals($oldHash, $artisan->getPassword(), 'The password hash has changed'); // Fails on plaintext
        self::assertEquals('New name', $artisan->getName(), 'The update did not actually happen');
    }

    public function testMakerUpdatedWithPasswordChangeHasUpdatedHash(): void
    {
        $artisan = self::getArtisan(
            name: 'Old name',
            makerId: 'MAKERID',
            password: 'old-password',
            contactAllowed: ContactPermit::NO,
            ages: Ages::MIXED,
            nsfwWebsite: false,
            nsfwSocial: false,
            worksWithMinors: true,
        );
        self::persistAndFlush($artisan);
        $oldHash = $artisan->getPassword();
        unset($artisan);

        $this->client->request('GET', '/iu_form/start/MAKERID');
        self::skipRulesAndCaptcha($this->client);

        $form = $this->client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]'                        => 'New name',
            'iu_form[password]'                    => 'new-password',
            'iu_form[changePassword]'              => '1',
            'iu_form[verificationAcknowledgement]' => '1',
        ]);
        $this::submitValid($this->client, $form);

        self::assertIuSubmittedWrongPasswordContactNotAllowed();

        self::performImport($this->client, true, 1);
        self::flushAndClear();

        $artisan = self::findArtisanByMakerId('MAKERID');
        self::assertNotEquals($oldHash, $artisan->getPassword(), 'The password was not changed');
        self::assertEquals('New name', $artisan->getName(), 'The update did not actually happen');
        self::assertTrue(password_verify('new-password', $artisan->getPassword()), 'Updated password fails');
    }

    public function testNotAcceptedMakerWithWrongPasswordDoesntGetUpdated(): void
    {
        $artisan = self::getArtisan(
            name: 'Old name',
            makerId: 'MAKERID',
            password: 'old-password',
            contactAllowed: ContactPermit::NO,
            ages: Ages::MIXED,
            nsfwWebsite: false,
            nsfwSocial: false,
            worksWithMinors: true,
        );
        self::persistAndFlush($artisan);
        $oldHash = $artisan->getPassword();
        unset($artisan);

        $this->client->request('GET', '/iu_form/start/MAKERID');
        self::skipRulesAndCaptcha($this->client);

        $form = $this->client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]'                        => 'New name',
            'iu_form[password]'                    => 'new-password',
            'iu_form[changePassword]'              => '1',
            'iu_form[verificationAcknowledgement]' => '1',
        ]);
        $this::submitValid($this->client, $form);

        self::assertIuSubmittedWrongPasswordContactNotAllowed();

        self::performImport($this->client, false, 1);
        self::flushAndClear();

        $artisan = self::findArtisanByMakerId('MAKERID');
        self::assertEquals($oldHash, $artisan->getPassword(), 'The password was actually changed');
        self::assertEquals('Old name', $artisan->getName(), 'The update actually happened');
    }
}
