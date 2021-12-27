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

        $client->request('GET', '/iu_form/fill/TEST');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('GET', '/iu_form/fill/TEST002');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('GET', '/iu_form/fill/TEST000');
        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testSubmittingEmptyDoesnt500(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/fill');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Submit')->form();
        $client->submit($form);

        self::assertResponseStatusCodeSame(422);
    }

    public function testErrorMessagesForRequiredFields(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/fill');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[contactAllowed]' => 'CORRECTIONS',
        ]);
        $client->submit($form);

        self::assertResponseStatusCodeSame(422);

        self::assertSelectorTextContains('#iu_form_name + .invalid-feedback', 'This value should not be blank.');
        self::assertSelectorTextContains('#iu_form_country + .invalid-feedback', 'This value should not be blank.');
        self::assertSelectorTextContains('#iu_form_ages + .help-text + .invalid-feedback', 'You must answer this question.');
        self::assertSelectorTextContains('#iu_form_worksWithMinors + .invalid-feedback', 'You must answer this question.');
        self::assertSelectorTextContains('#iu_form_makerId + .help-text + .invalid-feedback', 'This value should not be blank.');
        self::assertSelectorTextContains('#iu_form_contactInfoObfuscated + .help-text + .invalid-feedback', 'This value should not be blank.');
        self::assertSelectorTextContains('#iu_form_password + .help-text + .invalid-feedback', 'Password is required.');
    }

    public function testContactMethodNotRequiredWhenContactNotAllowed(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/fill');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]'            => 'test-maker-555',
            'iu_form[country]'         => 'Finland',
            'iu_form[ages]'            => 'ADULTS',
            'iu_form[worksWithMinors]' => 'NO',
            'iu_form[makerId]'         => 'MAKERID',
            'iu_form[contactAllowed]'  => 'FEEDBACK',
            'iu_form[password]'        => 'why-so-serious',
        ]);

        $client->submit($form);
        self::assertSelectorTextContains('#iu_form_contactInfoObfuscated_help + div.invalid-feedback', 'This value should not be blank.');

        $form = $client->getCrawler()->selectButton('Submit')->form();
        $form->get('iu_form[contactInfoObfuscated]')->setValue('email@address');
        $form->get('iu_form[password]')->setValue('why-so-serious');

        $client->submit($form);
        $client->followRedirect();

        self::assertSelectorTextContains('.alert-success h4', 'Your submission has been recorded!');
        self::assertSelectorTextContains('.alert-success', 'Submissions are typically processed once a week, during the weekend. If you don\'t see your changes on-line after 7 days');
    }

    public function testConfirmationNewMaker(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/fill');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]'            => 'test-maker-555',
            'iu_form[country]'         => 'Finland',
            'iu_form[ages]'            => 'ADULTS',
            'iu_form[worksWithMinors]' => 'NO',
            'iu_form[makerId]'         => 'MAKERID',
            'iu_form[contactAllowed]'  => 'NO',
            'iu_form[password]'        => 'why-so-serious',
        ]);

        $client->submit($form);
        $client->followRedirect();

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

        $client->request('GET', '/iu_form/fill/MAKERID');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Submit')->form();

        $form->get('iu_form[password]')->setValue('password-555');

        $client->submit($form);
        $client->followRedirect();

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

        $client->request('GET', '/iu_form/fill/MAKERID');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Submit')->form();

        $form->get('iu_form[password]')->setValue('password-554');
        $form->get('iu_form[contactInfoObfuscated]')->setValue('email@address');

        $client->submit($form);
        $client->followRedirect();

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

        $client->request('GET', '/iu_form/fill/MAKERID');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Submit')->form();

        $form->get('iu_form[password]')->setValue('password-554');
        $form->get('iu_form[contactAllowed]')->setValue('ANNOUNCEMENTS');
        $form->get('iu_form[contactInfoObfuscated]')->setValue('email@address');

        $client->submit($form);
        $client->followRedirect();

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

        $client->request('GET', '/iu_form/fill/MAKERID');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Submit')->form();

        $form->get('iu_form[password]')->setValue('password-554');
        $form->get('iu_form[contactAllowed]')->setValue('NO');

        $client->submit($form);
        $client->followRedirect();

        self::assertSelectorTextContains('.alert-danger h4', 'Your submission has been recorded, but...');
        self::assertSelectorTextContains('.alert-danger', 'The password you provided didn\'t match, and you didn\'t agree to be contacted');
    }
}
