<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\DataDefinitions\Ages;
use App\DataDefinitions\ContactPermit;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;

class PasswordHandlingTest extends AbstractTestWithEM
{
    use IuFormTrait;

    public function testNewMakerPasswordIsHashed(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($client);
        self::skipData($client, true);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[contactAllowed]' => 'NO',
            'iu_form[password]'       => 'some-password',
        ]);
        $this::submitValid($client, $form);

        self::assertSelectorTextContains('h4', 'Your submission has been recorded!');

        self::performImport($client, true, 1);
        self::flushAndClear();

        $artisan = self::findArtisanByMakerId('TESTMID');
        self::assertTrue(password_verify('some-password', $artisan->getPassword())); // Fails on plaintext
    }

    public function testMakerUpdatedWithCorrectPasswordHasUnchangedHash(): void
    {
        $client = static::createClient();

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

        $client->request('GET', '/iu_form/start/MAKERID');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Continue')->form([
            'iu_form[name]' => 'New name',
        ]);
        $this::submitValid($client, $form);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[password]' => 'known-password',
        ]);
        $this::submitValid($client, $form);

        self::assertSelectorTextContains('h4', 'Your submission has been recorded!');

        self::performImport($client, true, 1);
        self::flushAndClear();

        $artisan = self::findArtisanByMakerId('MAKERID');
        self::assertEquals($oldHash, $artisan->getPassword(), 'The password hash has changed'); // Fails on plaintext
        self::assertEquals('New name', $artisan->getName(), 'The update did not actually happen');
    }

    public function testMakerUpdatedWithPasswordChangeHasUpdatedHash(): void
    {
        $client = static::createClient();

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

        $client->request('GET', '/iu_form/start/MAKERID');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Continue')->form([
            'iu_form[name]' => 'New name',
        ]);
        $this::submitValid($client, $form);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[password]'       => 'new-password',
            'iu_form[changePassword]' => '1',
        ]);
        $this::submitValid($client, $form);

        self::assertSelectorTextContains('h4', 'Your submission has been recorded, but...');

        self::performImport($client, true, 1);
        self::flushAndClear();

        $artisan = self::findArtisanByMakerId('MAKERID');
        self::assertNotEquals($oldHash, $artisan->getPassword(), 'The password was not changed');
        self::assertEquals('New name', $artisan->getName(), 'The update did not actually happen');
        self::assertTrue(password_verify('new-password', $artisan->getPassword()), 'Updated password fails');
    }

    public function testNotAcceptedMakerWithWrongPasswordDoesntGetUpdated(): void
    {
        $client = static::createClient();

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

        $client->request('GET', '/iu_form/start/MAKERID');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Continue')->form([
            'iu_form[name]' => 'New name',
        ]);
        $this::submitValid($client, $form);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[password]'       => 'new-password',
            'iu_form[changePassword]' => '1',
        ]);
        $this::submitValid($client, $form);

        self::assertSelectorTextContains('h4', 'Your submission has been recorded, but...');

        self::performImport($client, false, 1);
        self::flushAndClear();

        $artisan = self::findArtisanByMakerId('MAKERID');
        self::assertEquals($oldHash, $artisan->getPassword(), 'The password was actually changed');
        self::assertEquals('Old name', $artisan->getName(), 'The update actually happened');
    }
}
