<?php

declare(strict_types=1);

namespace App\Tests\Frontend;

use App\Tests\TestUtils\DbEnabledPantherTestCase;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;

class SmokeUiTest extends DbEnabledPantherTestCase
{
    /**
     * @throws WebDriverException
     */
    public function testMainPageLoadsCorrectly(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/');
        $client->waitForVisibility('#scam-risk-warning', 5);
        $client->findElement(WebDriverBy::id('scam-risk-acknowledgement'))->click();
        $client->waitForVisibility('#artisans', 2);
        $client->findElement(WebDriverBy::id('filtersButton'))->click();
        $client->waitForVisibility('#filtersTitle', 2);

        self::assertNotEmpty('We are here, so whole scenario succeeded');
    }

    public function testFiltersWorkInGeneral(): void
    {
        self::assertTrue(true); // TODO
    }

    public function testDetailsModalWorksInGeneral(): void
    {
        self::assertTrue(true); // TODO
    }
}
