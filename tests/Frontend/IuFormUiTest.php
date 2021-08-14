<?php

declare(strict_types=1);

namespace App\Tests\Frontend;

use App\Tests\TestUtils\DbEnabledPantherTestCase;
use Exception;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;

class IuFormUiTest extends DbEnabledPantherTestCase
{
    /**
     * @throws WebDriverException|Exception
     */
    public function testIForgotPasswordShowsHelp(): void
    {
        $client = static::createPantherClient(['hostname' => 'fuzzrake']);
        self::setWindowSize($client, 1600, 900);

        self::persistAndFlush(self::getArtisan(makerId: 'MAKERID'));

        $client->request('GET', '/iu_form/fill/MAKERID');
        $client->waitForVisibility('#iu_form_changePassword', 5);


        $client->getKeyboard()->pressKey(WebDriverKeys::END)->pressKey(WebDriverKeys::PAGE_UP)
            ->pressKey(WebDriverKeys::DOWN); // grep-ugly-tests-workarounds Workaround for element not visible bug
        usleep(100000); // grep-ugly-tests-workarounds Workaround for element not visible bug

        self::assertSelectorIsNotVisible('#forgotten_password_instructions');
        $client->findElement(WebDriverBy::id('iu_form_changePassword'))->click();
        self::assertSelectorIsVisible('#forgotten_password_instructions');
    }

    /**
     * @throws WebDriverException|Exception
     */
    public function testContactMethodNotRequiredAndHiddenWhenContactNotAllowed(): void
    {
        $client = static::createPantherClient(['hostname' => 'fuzzrake']);
        self::setWindowSize($client, 1600, 900);

        $client->request('GET', '/iu_form/fill');
        $client->waitForVisibility('#iu_form_contactInfoObfuscated', 5);

        $client->getKeyboard()->pressKey(WebDriverKeys::END)->pressKey(WebDriverKeys::PAGE_UP)
            ->pressKey(WebDriverKeys::DOWN); // grep-ugly-tests-workarounds Workaround for element not visible bug
        usleep(100000); // grep-ugly-tests-workarounds Workaround for element not visible bug

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
