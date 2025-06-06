<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class PasswordHandlingTest extends IuSubmissionsTestCase
{
    use IuFormTrait;

    public function testNewCreatorPasswordIsHashed(): void
    {
        self::$client->request('GET', '/iu_form/start');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[creatorId]' => 'TEST001',
            'iu_form[name]' => 'Test name',
            'iu_form[country]' => 'Test country',
            'iu_form[ages]' => 'MIXED',
            'iu_form[nsfwWebsite]' => 'NO',
            'iu_form[nsfwSocial]' => 'NO',
            'iu_form[worksWithMinors]' => 'NO',
            'iu_form[contactAllowed]' => 'NO',
            'iu_form[password]' => 'some-password',
            $this->getCaptchaFieldName('right') => 'right',
        ]);
        self::submitValid($form);

        self::assertIuSubmittedCorrectPassword();

        self::performImport(true, 1);
        self::flushAndClear();

        $creator = self::findCreatorByCreatorId('TEST001');
        self::assertTrue(password_verify('some-password', $creator->getPassword())); // Fails on plaintext
    }

    public function testCreatorUpdatedWithCorrectPasswordHasUnchangedHash(): void
    {
        $creator = self::getCreator(
            name: 'Old name',
            creatorId: 'TEST001',
            password: 'known-password',
            contactAllowed: ContactPermit::NO,
            ages: Ages::MIXED,
            nsfwWebsite: false,
            nsfwSocial: false,
            worksWithMinors: true,
        );
        self::persistAndFlush($creator);
        $oldHash = $creator->getPassword();
        unset($creator);

        self::$client->request('GET', '/iu_form/start/TEST001');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]'     => 'New name',
            'iu_form[password]' => 'known-password',
            $this->getCaptchaFieldName('right') => 'right',
        ]);
        self::submitValid($form);

        self::assertIuSubmittedCorrectPassword();

        self::performImport(true, 1);
        self::flushAndClear();

        $creator = self::findCreatorByCreatorId('TEST001');
        self::assertSame($oldHash, $creator->getPassword(), 'The password hash has changed'); // Fails on plaintext
        self::assertSame('New name', $creator->getName(), 'The update did not actually happen');
    }

    public function testCreatorUpdatedWithPasswordChangeHasUpdatedHash(): void
    {
        $creator = self::getCreator(
            name: 'Old name',
            creatorId: 'TEST001',
            password: 'old-password',
            contactAllowed: ContactPermit::NO,
            ages: Ages::MIXED,
            nsfwWebsite: false,
            nsfwSocial: false,
            worksWithMinors: true,
        );
        self::persistAndFlush($creator);
        $oldHash = $creator->getPassword();
        unset($creator);

        self::$client->request('GET', '/iu_form/start/TEST001');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]'                        => 'New name',
            'iu_form[password]'                    => 'new-password',
            'iu_form[changePassword]'              => '1',
            'iu_form[verificationAcknowledgement]' => '1',
            $this->getCaptchaFieldName('right') => 'right',
        ]);
        self::submitValid($form);

        self::assertIuSubmittedWrongPasswordContactNotAllowed();

        self::performImport(true, 1);
        self::flushAndClear();

        $creator = self::findCreatorByCreatorId('TEST001');
        self::assertNotSame($oldHash, $creator->getPassword(), 'The password was not changed');
        self::assertSame('New name', $creator->getName(), 'The update did not actually happen');
        self::assertTrue(password_verify('new-password', $creator->getPassword()), 'Updated password fails');
    }

    public function testNotAcceptedCreatorWithWrongPasswordDoesntGetUpdated(): void
    {
        $creator = self::getCreator(
            name: 'Old name',
            creatorId: 'TEST001',
            password: 'old-password',
            contactAllowed: ContactPermit::NO,
            ages: Ages::MIXED,
            nsfwWebsite: false,
            nsfwSocial: false,
            worksWithMinors: true,
        );
        self::persistAndFlush($creator);
        $oldHash = $creator->getPassword();
        unset($creator);

        self::$client->request('GET', '/iu_form/start/TEST001');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]'                        => 'New name',
            'iu_form[password]'                    => 'new-password',
            'iu_form[changePassword]'              => '1',
            'iu_form[verificationAcknowledgement]' => '1',
            $this->getCaptchaFieldName('right') => 'right',
        ]);
        self::submitValid($form);

        self::assertIuSubmittedWrongPasswordContactNotAllowed();

        self::performImport(false, 1);
        self::flushAndClear();

        $creator = self::findCreatorByCreatorId('TEST001');
        self::assertSame($oldHash, $creator->getPassword(), 'The password was actually changed');
        self::assertSame('Old name', $creator->getName(), 'The update actually happened');
    }
}
