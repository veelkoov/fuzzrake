<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Tests\TestUtils\Cases\Traits\IuFormTrait;

/**
 * @medium
 */
class IuNavigationTest extends AbstractTestWithEM // TODO: Ultimately get rid of this. Instead test JS behavior in browser. This will no longer be a "navigation" test as well.
{
    use IuFormTrait;

    public function testAbortWorksOnDataPage(): void
    {
        $this->client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($this->client);

        $this::submitInvalidForm($this->client, 'Submit', [
            'iu_form[name]' => 'Some name',
            'iu_form[ages]' => 'ADULTS',
        ]);

        self::assertInputValueSame('iu_form[name]', 'Some name', "Partial data hasn't been saved");

        $this::submitValidForm($this->client, 'Start over or withdraw', []);

        self::assertSelectorTextContains('h1', 'Inclusion/update request', "Haven't been redirected back");

        self::skipRulesAndCaptcha($this->client);

        self::assertInputValueSame('iu_form[name]', '', 'Previously set "name" value got preserved');
    }
}
