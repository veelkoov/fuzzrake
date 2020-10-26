<?php

declare(strict_types=1);

namespace App\Tests\Frontend;

use App\Tests\TestUtils\DbEnabledPantherTestCase;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;

class BasicUiTest extends DbEnabledPantherTestCase
{
    /**
     * @throws WebDriverException
     */
    public function testEverythingLoadsCorrectly(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', 'http://localhost:8080/');
        $client->waitForVisibility('#artisans', 5);
        $client->findElement(WebDriverBy::id('filtersButton'))->click();
        $client->waitForVisibility('#filtersTitle', 2);

        self::assertNotEmpty("We are here, so whole scenario succeeded");
    }

    public function testFilters(): void
    {
        self::assertTrue(true); // TODO
    }

    public function testDetailsModal(): void
    {
        self::assertTrue(true); // TODO
    }
}
