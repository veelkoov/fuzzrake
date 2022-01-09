<?php

declare(strict_types=1);

namespace App\Tests\Frontend;

use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;

class MainWithEMPageUiSmokeTest extends PantherTestCaseWithEM
{
    /**
     * @throws WebDriverException
     */
    public function testMainPageUiSmoke(): void
    {
        $client = static::createPantherClient();
        self::setWindowSize($client, 1600, 900);

        self::persistAndFlush(
            self::getArtisan('Test artisan 1', 'TEST001', 'CZ'),
            self::getArtisan('Test artisan 2', 'TEST002', 'CA'),
            self::getArtisan('Test artisan 3', 'TEST003', 'DE'),
        );

        $client->request('GET', '/');
        $client->waitForVisibility('#scam-risk-warning', 5);
        $this->screenshot($client);

        $client->findElement(WebDriverBy::id('scam-risk-acknowledgement'))->click();
        $client->waitForVisibility('#artisans', 5);
        $this->screenshot($client);

        self::assertStringContainsString('Displaying 3 out of 3 fursuit makers in the database.', $client->getCrawler()->findElement(WebDriverBy::id('artisans_info'))->getText());

        $client->findElement(WebDriverBy::id('filtersButton'))->click();
        $client->waitForVisibility('#filtersTitle', 5);
        $this->screenshot($client);

        $client->findElement(WebDriverBy::cssSelector('#filter-ctrl-countries > button'))->click();
        $client->waitForVisibility('input[type=checkbox][value=CZ]', 5);
        $this->screenshot($client);

        $client->findElement(WebDriverBy::xpath('//legend[contains(text(), "Europe")]//a[@data-action="all"]'))->click();
        $client->findElement(WebDriverBy::xpath('//button[text() = "Apply"]'))->click();
        $client->waitFor('//div[@id="artisans_info"]/p[contains(text(), "Displaying 2 out of 3 fursuit makers in the database.")]', 5);
        $this->screenshot($client);

        $client->findElement(WebDriverBy::xpath('//td[contains(text(), "Test artisan 1")]'))->click();
        $client->waitForVisibility('//a[@id="makerId" and @href="#TEST001"]', 5);
        $this->screenshot($client);

        self::assertStringContainsString('Test artisan 1', $client->getCrawler()->findElement(WebDriverBy::id('artisanName'))->getText());

        $client->findElement(WebDriverBy::xpath('//div[@id="artisanDetailsModalContent"]//button[text() = "Data outdated/inaccurate?"]'))->click();

        $client->waitForVisibility('#artisanUpdatesModalContent', 5);
        $this->screenshot($client);
        self::assertStringContainsString('Test artisan 1', $client->getCrawler()->findElement(WebDriverBy::id('updateRequestLabel'))->getText());

        $client->findElement(WebDriverBy::cssSelector('#artisanUpdatesModalContent .modal-footer > button'))->click();
        $client->waitForInvisibility('#artisanUpdatesModalContent', 5);
        $this->screenshot($client);

        $client->findElement(WebDriverBy::cssSelector('#TEST003 td.links div.btn-group > button'))->click();
        $client->waitForVisibility('#TEST003 td.links div.btn-group > ul li:last-child > a', 5);
        $this->screenshot($client);

        $client->findElement(WebDriverBy::cssSelector('#TEST003 td.links div.btn-group > ul li:last-child > a'))->click();
        $client->waitForVisibility('#artisanUpdatesModalContent', 5);
        $this->screenshot($client);

        self::assertStringContainsString('Test artisan 3', $client->getCrawler()->findElement(WebDriverBy::id('updateRequestLabel'))->getText());
    }
}
