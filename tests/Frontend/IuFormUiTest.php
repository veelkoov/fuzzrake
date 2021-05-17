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
        $client->waitForInvisibility('#forgotten_password_instructions', 1);
        $client->findElement(WebDriverBy::id('forgotten_password'))->click();
        $client->waitForVisibility('#forgotten_password_instructions', 1);
        self::assertTrue(true); // If we are here, everything worked as expected
    }
}
