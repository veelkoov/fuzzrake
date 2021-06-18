<?php

declare(strict_types=1);

namespace App\Tests\Frontend;

use App\Tests\TestUtils\DbEnabledPantherTestCase;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;

class IuFormUiTest extends DbEnabledPantherTestCase
{
    /**
     * @throws WebDriverException
     */
    public function testIForgotPasswordShowsHelp(): void
    {
        $client = static::createPantherClient();
        self::setWindowSize($client, 1600, 900);

        self::persistAndFlush(self::getArtisan(makerId: 'MAKERID'));

        $client->request('GET', '/iu_form/fill/MAKERID');
        $client->waitForVisibility('#forgotten_password', 5);

        self::assertSelectorIsNotVisible('#forgotten_password_instructions');
        $client->findElement(WebDriverBy::id('forgotten_password'))->click();
        self::assertSelectorIsVisible('#forgotten_password_instructions');
    }

    /**
     * @throws WebDriverException
     */
    public function testContactMethodNotRequiredAndHiddenWhenContactNotAllowed(): void
    {
        $client = static::createPantherClient();
        self::setWindowSize($client, 1600, 900);

        $client->request('GET', '/iu_form/fill');
        $client->waitForVisibility('#iu_form_contactInfoObfuscated', 5);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[contactAllowed]' => 'FEEDBACK',
        ]);

        self::assertSelectorIsVisible('#iu_form_contactInfoObfuscated');
        self::assertSelectorExists('#iu_form_contactInfoObfuscated[required]');

        $form->setValues([
            'iu_form[contactAllowed]' => 'NO',
        ]);

        self::assertSelectorIsNotVisible('#iu_form_contactInfoObfuscated');
        self::assertSelectorExists('#iu_form_contactInfoObfuscated:not([required])');
    }
}
