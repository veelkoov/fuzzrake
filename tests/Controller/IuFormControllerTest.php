<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\DbEnabledWebTestCase;
use App\Utils\Artisan\ContactPermit;

class IuFormControllerTest extends DbEnabledWebTestCase
{
    public function testIuForm(): void
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

    public function testContactMethodNotRequiredWhenContactNotAllowed(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/fill');
        $form = $client->getCrawler()->selectButton('Submit')->form();

        $form->get('iu_form[name]')->setValue('test-maker-555');
        $form->get('iu_form[country]')->setValue('Finland');
        $form->get('iu_form[makerId]')->setValue('MAKERID');
        $form->get('iu_form[contactAllowed]')->setValue('FEEDBACK');
        $form->get('iu_form[passcode]')->setValue('why-so-serious');

        $client->submit($form);
        self::assertSelectorTextContains('label[for="iu_form_contactInfoObfuscated"] .form-error-message', 'This value should not be blank.');

        $form = $client->getCrawler()->selectButton('Submit')->form();
        $form->get('iu_form[contactInfoObfuscated]')->setValue('email@address');
        $form->get('iu_form[passcode]')->setValue('why-so-serious');

        $client->submit($form);
        $client->followRedirect();

        self::assertSelectorTextContains('.alert-success h4', 'Your submission has been recorded!');
        self::assertSelectorTextContains('.alert-success', 'Submissions are typically processed once a week, during the weekend. If you don\'t see your changes on-line after 7 days');
    }

    public function testConfirmationNewMaker(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/fill');
        $form = $client->getCrawler()->selectButton('Submit')->form();

        $form->get('iu_form[name]')->setValue('test-maker-555');
        $form->get('iu_form[country]')->setValue('Finland');
        $form->get('iu_form[makerId]')->setValue('MAKERID');
        $form->get('iu_form[contactAllowed]')->setValue('NO');
        $form->get('iu_form[passcode]')->setValue('why-so-serious');

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
        ));

        $client->request('GET', '/iu_form/fill/MAKERID');
        $form = $client->getCrawler()->selectButton('Submit')->form();

        $form->get('iu_form[passcode]')->setValue('password-555');

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
        ));

        $client->request('GET', '/iu_form/fill/MAKERID');
        $form = $client->getCrawler()->selectButton('Submit')->form();

        $form->get('iu_form[passcode]')->setValue('password-554');
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
        ));

        $client->request('GET', '/iu_form/fill/MAKERID');
        $form = $client->getCrawler()->selectButton('Submit')->form();

        $form->get('iu_form[passcode]')->setValue('password-554');
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
        ));

        $client->request('GET', '/iu_form/fill/MAKERID');
        $form = $client->getCrawler()->selectButton('Submit')->form();

        $form->get('iu_form[passcode]')->setValue('password-554');
        $form->get('iu_form[contactAllowed]')->setValue('NO');

        $client->submit($form);
        $client->followRedirect();

        self::assertSelectorTextContains('.alert-danger h4', 'Your submission has been recorded, but...');
        self::assertSelectorTextContains('.alert-danger', 'The password you provided didn\'t match, and you didn\'t agree to be contacted');
    }
}
