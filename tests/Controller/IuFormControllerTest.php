<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataDefinitions\ContactPermit;
use App\Tests\TestUtils\DbEnabledWebTestCase;
use App\Tests\TestUtils\IuFormTrait;

class IuFormControllerTest extends DbEnabledWebTestCase
{
    use IuFormTrait;

    public function testIuFormLoadsForExistingMakers(): void
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/iu_form/start/TEST');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('GET', '/iu_form/start/TEST002');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('GET', '/iu_form/start/TEST000');
        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testSubmittingEmptyDoesnt500(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Submit')->form();
        self::submitInvalid($client, $form);

        self::skipData($client, true);

        $form = $client->getCrawler()->selectButton('Submit')->form();
        self::submitInvalid($client, $form);
    }

    public function testErrorMessagesForRequiredFields(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Submit')->form();
        self::submitInvalid($client, $form);

        self::assertSelectorTextContains('#iu_form_name + .invalid-feedback', 'This value should not be blank.');
        self::assertSelectorTextContains('#iu_form_country + .invalid-feedback', 'This value should not be blank.');
        self::assertSelectorTextContains('#iu_form_ages + .help-text + .invalid-feedback', 'You must answer this question.');
        self::assertSelectorTextContains('#iu_form_worksWithMinors + .invalid-feedback', 'You must answer this question.');
        self::assertSelectorTextContains('#iu_form_makerId + .help-text + .invalid-feedback', 'This value should not be blank.');

        self::skipData($client, true);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[contactAllowed]' => 'CORRECTIONS',
        ]);
        self::submitInvalid($client, $form);

        self::assertSelectorTextContains('#iu_form_contactInfoObfuscated + .help-text + .invalid-feedback', 'This value should not be blank.');
        self::assertSelectorTextContains('#iu_form_password + .help-text + .invalid-feedback', 'Password is required.');
    }

    public function testContactMethodNotRequiredWhenContactNotAllowed(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($client);
        self::skipData($client, true);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[contactAllowed]'  => 'FEEDBACK',
            'iu_form[password]'        => 'why-so-serious',
        ]);
        self::submitInvalid($client, $form);

        self::assertSelectorTextContains('#iu_form_contactInfoObfuscated_help + div.invalid-feedback', 'This value should not be blank.');

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[contactInfoObfuscated]' => 'email@address',
            'iu_form[password]'              => 'why-so-serious',
        ]);
        self::submitValid($client, $form);

        self::assertSelectorTextContains('.alert-success h4', 'Your submission has been recorded!');
        self::assertSelectorTextContains('.alert-success', 'Submissions are typically processed once a week, during the weekend. If you don\'t see your changes on-line after 7 days');
    }

    public function testConfirmationNewMaker(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($client);
        self::skipData($client, true);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[contactAllowed]'  => 'NO',
            'iu_form[password]'        => 'why-so-serious',
        ]);
        self::submitValid($client, $form);

        self::assertSelectorTextContains('.alert-success h4', 'Your submission has been recorded!');
        self::assertSelectorTextContains('.alert-success', 'Submissions are typically processed once a week, during the weekend. If you don\'t see your changes on-line after 7 days');
    }

    public function testConfirmationValidPassword(): void
    {
        $client = static::createClient();

        self::persistAndFlush(self::getArtisan(
            makerId: 'MAKERID',
            password: 'password-555',
            contactAllowed: 'NO',
            ages: 'MIXED',
            worksWithMinors: true,
        ));

        $client->request('GET', '/iu_form/start/MAKERID');
        self::skipRulesAndCaptcha($client);
        self::skipData($client, false);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[password]' => 'password-555',
        ]);
        self::submitValid($client, $form);

        self::assertSelectorTextContains('.alert-success h4', 'Your submission has been recorded!');
        self::assertSelectorTextContains('.alert-success', 'Submissions are typically processed once a week, during the weekend. If you don\'t see your changes on-line after 7 days');
    }

    public function testConfirmationInvalidPasswordContactAllowed(): void
    {
        $client = static::createClient();

        self::persistAndFlush(self::getArtisan(
            makerId: 'MAKERID',
            password: 'password-555',
            contactAllowed: 'CORRECTIONS',
            ages: 'ADULTS',
            worksWithMinors: true,
        ));

        $client->request('GET', '/iu_form/start/MAKERID');
        self::skipRulesAndCaptcha($client);
        self::skipData($client, false);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[password]'              => 'password-554',
            'iu_form[contactInfoObfuscated]' => 'email@address',
        ]);
        self::submitValid($client, $form);

        self::assertSelectorTextContains('.alert-warning h4', 'Your submission has been recorded, but...');
        self::assertSelectorTextContains('.alert-warning', 'The password you provided didn\'t match, so expect to be contacted by the maintainer to confirm your changes.');
    }

    public function testConfirmationInvalidPasswordContactIsNotAllowed(): void
    {
        $client = static::createClient();

        self::persistAndFlush(self::getArtisan(
            makerId: 'MAKERID',
            password: 'password-555',
            contactAllowed: 'NO',
            ages: 'ADULTS',
            worksWithMinors: true,
        ));

        $client->request('GET', '/iu_form/start/MAKERID');
        self::skipRulesAndCaptcha($client);
        self::skipData($client, false);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[password]'              => 'password-554',
            'iu_form[contactAllowed]'        => 'ANNOUNCEMENTS',
            'iu_form[contactInfoObfuscated]' => 'email@address',
        ]);
        self::submitValid($client, $form);

        self::assertSelectorTextContains('.alert-danger h4', 'Your submission has been recorded, but...');
        self::assertSelectorTextContains('.alert-danger', 'The password you provided didn\'t match, and you previously didn\'t agree to be contacted');
    }

    public function testConfirmationInvalidPasswordContactWasNotAllowed(): void
    {
        $client = static::createClient();

        self::persistAndFlush(self::getArtisan(
            makerId: 'MAKERID',
            password: 'password-555',
            contactAllowed: ContactPermit::CORRECTIONS,
            ages: 'MINORS',
            worksWithMinors: true,
        ));

        $client->request('GET', '/iu_form/start/MAKERID');
        self::skipRulesAndCaptcha($client);
        self::skipData($client, false);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[password]'       => 'password-554',
            'iu_form[contactAllowed]' => 'NO',
        ]);
        self::submitValid($client, $form);

        self::assertSelectorTextContains('.alert-danger h4', 'Your submission has been recorded, but...');
        self::assertSelectorTextContains('.alert-danger', 'The password you provided didn\'t match, and you didn\'t agree to be contacted');
    }
}
