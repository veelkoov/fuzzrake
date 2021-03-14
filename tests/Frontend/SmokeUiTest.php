<?php

declare(strict_types=1);

namespace App\Tests\Frontend;

use App\Tests\TestUtils\DbEnabledPantherTestCase;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;

class SmokeUiTest extends DbEnabledPantherTestCase
{
    /**
     * @throws WebDriverException
     */
    public function testMainPage(): void
    {
        $client = static::createPantherClient();
        $client->manage()->window()->setSize(new WebDriverDimension(1600, 900));

        self::persistAndFlush(
            self::getArtisan('Test artisan 1', 'TEST001', 'CZ'),
            self::getArtisan('Test artisan 2', 'TEST002', 'CA'),
            self::getArtisan('Test artisan 3', 'TEST003', 'DE'),
        );

        $client->request('GET', '/');
        $client->waitForVisibility('#scam-risk-warning', 5);
        $client->findElement(WebDriverBy::id('scam-risk-acknowledgement'))->click();
        $client->waitForVisibility('#artisans', 5);
        self::assertStringContainsString('Displaying 3 out of 3 fursuit makers in the database.', $client->getCrawler()->findElement(WebDriverBy::id('artisans_info'))->getText());
        $client->findElement(WebDriverBy::id('filtersButton'))->click();
        $client->waitForVisibility('#filtersTitle', 2);
        $client->findElement(WebDriverBy::cssSelector('#filter-ctrl-countries > button'))->click();
        $client->waitForVisibility('#countryCheckBoxCZ', 2);
        $client->findElement(WebDriverBy::xpath('//legend[contains(text(), "Europe")]//a[@data-action="all"]'))->click();
        $client->findElement(WebDriverBy::xpath('//button[text() = "Apply"]'))->click();
        $client->waitFor('//div[@id="artisans_info"]/p[contains(text(), "Displaying 2 out of 3 fursuit makers in the database.")]', 1);
        $client->findElement(WebDriverBy::xpath('//td[contains(text(), "Test artisan 1")]'))->click();
        $client->refreshCrawler();
        $client->waitForVisibility('//a[@id="makerId" and @href="#TEST001"]', 1);
        self::assertStringContainsString('Test artisan 1', $client->getCrawler()->findElement(WebDriverBy::id('artisanName'))->getText());
    }
}
