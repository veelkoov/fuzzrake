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
        $this->client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($this->client);

        $this::submitInvalidForm($this->client, 'Continue', [
            'iu_form[name]' => 'Some name',
            'iu_form[ages]' => 'ADULTS',
        ]);

        self::assertInputValueSame('iu_form[name]', 'Some name', "Partial data hasn't been saved");

        $this::submitValidForm($this->client, 'Start over or withdraw', []);

        self::assertSelectorTextContains('h1', 'Inclusion/update request', "Haven't been redirected back");

        self::skipRulesAndCaptcha($this->client);

        self::assertInputValueSame('iu_form[name]', '', 'Previously set "name" value got preserved');
    }

    public function testAbortWorksOnContactAndPasswordPage(): void
    {
        $this->client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($this->client);
        self::skipData($this->client, true);

        $this::submitInvalidForm($this->client, 'Submit', [
            'iu_form[contactAllowed]'         => 'FEEDBACK',
            'iu_form[emailAddressObfuscated]' => 'test-some-email@example.com',
        ]);

        self::assertInputValueSame('iu_form[emailAddressObfuscated]', 'test-some-email@example.com', "Partial data hasn't been saved");

        $this::submitValidForm($this->client, 'Start over or withdraw', []);

        self::assertSelectorTextContains('h1', 'Inclusion/update request', "Haven't been redirected back");

        self::skipRulesAndCaptcha($this->client);

        self::assertInputValueSame('iu_form[name]', '', 'Previously set "name" value got preserved');

        self::skipData($this->client, true);

        self::assertInputValueSame('iu_form[emailAddressObfuscated]', '', 'Previously set "emailAddressObfuscated" value got preserved');
    }

    /**
     * @throws Exception
     */
    public function testBackWorksOnDataPage(): void
    {
        $this->client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($this->client);

        $this::submitValidForm($this->client, 'Continue', [
            'iu_form[name]'            => 'test-some-name',
            'iu_form[ages]'            => 'ADULTS',
            'iu_form[nsfwWebsite]'     => 'NO',
            'iu_form[nsfwSocial]'      => 'NO',
            'iu_form[doesNsfw]'        => 'NO',
            'iu_form[worksWithMinors]' => 'NO',
            'iu_form[country]'         => 'FI',
            'iu_form[makerId]'         => 'ABRTEST',
        ]);

        $this::submitValidForm($this->client, 'Back', [
            'iu_form[contactAllowed]'         => 'FEEDBACK',
            'iu_form[emailAddressObfuscated]' => 'test-some-email@example.com',
        ]);

        self::assertSelectorTextContains('h2', 'Few instructions and tips', "Haven't been redirected back");
        self::assertInputValueSame('iu_form[name]', 'test-some-name', 'Previously set "name" value not preserved');

        $this::submitValidForm($this->client, 'Continue', []);
        self::assertInputValueSame('iu_form[emailAddressObfuscated]', 'test-some-email@example.com', 'Previously set "emailAddressObfuscated" value not preserved');

        $this::submitValidForm($this->client, 'Submit', [
            'iu_form[password]' => 'test-some-password',
        ]);

        self::performImport($this->client, true, 1);
        self::flushAndClear();

        $artisan = self::findArtisanByMakerId('ABRTEST');
        self::assertEquals('test-some-name', $artisan->getName());
        self::assertEquals('test-some-email@example.com', $artisan->getEmailAddress());
    }
}
