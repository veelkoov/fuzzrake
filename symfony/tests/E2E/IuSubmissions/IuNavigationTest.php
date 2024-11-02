<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use Exception;

/**
 * @medium
 */
class IuNavigationTest extends AbstractTestWithEM
{
    use IuFormTrait;

    public function testAbortWorksOnDataPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($client);

        $this::submitInvalidForm($client, 'Continue', [
            'iu_form[name]' => 'Some name',
            'iu_form[ages]' => 'ADULTS',
        ]);

        self::assertInputValueSame('iu_form[name]', 'Some name', "Partial data hasn't been saved");

        $this::submitValidForm($client, 'Start over or withdraw', []);

        self::assertSelectorTextContains('h1', 'Inclusion/update request', "Haven't been redirected back");

        self::skipRulesAndCaptcha($client);

        self::assertInputValueSame('iu_form[name]', '', 'Previously set "name" value got preserved');
    }

    public function testAbortWorksOnContactAndPasswordPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($client);
        self::skipData($client, true);

        $this::submitInvalidForm($client, 'Submit', [
            'iu_form[contactAllowed]'         => 'FEEDBACK',
            'iu_form[emailAddressObfuscated]' => 'test-some-email@example.com',
        ]);

        self::assertInputValueSame('iu_form[emailAddressObfuscated]', 'test-some-email@example.com', "Partial data hasn't been saved");

        $this::submitValidForm($client, 'Start over or withdraw', []);

        self::assertSelectorTextContains('h1', 'Inclusion/update request', "Haven't been redirected back");

        self::skipRulesAndCaptcha($client);

        self::assertInputValueSame('iu_form[name]', '', 'Previously set "name" value got preserved');

        self::skipData($client, true);

        self::assertInputValueSame('iu_form[emailAddressObfuscated]', '', 'Previously set "emailAddressObfuscated" value got preserved');
    }

    /**
     * @throws Exception
     */
    public function testBackWorksOnDataPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($client);

        $this::submitValidForm($client, 'Continue', [
            'iu_form[name]'            => 'test-some-name',
            'iu_form[ages]'            => 'ADULTS',
            'iu_form[nsfwWebsite]'     => 'NO',
            'iu_form[nsfwSocial]'      => 'NO',
            'iu_form[doesNsfw]'        => 'NO',
            'iu_form[worksWithMinors]' => 'NO',
            'iu_form[country]'         => 'FI',
            'iu_form[makerId]'         => 'ABRTEST',
        ]);

        $this::submitValidForm($client, 'Back', [
            'iu_form[contactAllowed]'         => 'FEEDBACK',
            'iu_form[emailAddressObfuscated]' => 'test-some-email@example.com',
        ]);

        self::assertSelectorTextContains('h2', 'Few instructions and tips', "Haven't been redirected back");
        self::assertInputValueSame('iu_form[name]', 'test-some-name', 'Previously set "name" value not preserved');

        $this::submitValidForm($client, 'Continue', []);
        self::assertInputValueSame('iu_form[emailAddressObfuscated]', 'test-some-email@example.com', 'Previously set "emailAddressObfuscated" value not preserved');

        $this::submitValidForm($client, 'Submit', [
            'iu_form[password]' => 'test-some-password',
        ]);

        self::performImport($client, true, 1);
        self::flushAndClear();

        $artisan = self::findArtisanByMakerId('ABRTEST');
        self::assertEquals('test-some-name', $artisan->getName());
        self::assertEquals('test-some-email@example.com', $artisan->getEmailAddress());
    }
}
