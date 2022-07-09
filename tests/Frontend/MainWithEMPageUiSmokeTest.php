<?php

declare(strict_types=1);

namespace App\Tests\Frontend;

use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use App\Tests\TestUtils\Paths;
use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Panther\Client;

class MainWithEMPageUiSmokeTest extends PantherTestCaseWithEM
{
    /**
     * @throws Exception
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

        (new Filesystem())->remove(Paths::getTestCacheDir());

        $client->request('GET', '/');
        $client->waitForVisibility('#scam-risk-warning', 5);

        $client->findElement(WebDriverBy::id('scam-risk-acknowledgement'))->click();
        $client->waitForVisibility('#artisans', 5);

        self::assertStringContainsString('Displaying 3 out of 3 fursuit makers in the database.', $client->getCrawler()->findElement(WebDriverBy::id('artisans_info'))->getText());

        $client->findElement(WebDriverBy::id('filtersButton'))->click();
        $client->waitForVisibility('#filtersTitle', 5);

        $client->findElement(WebDriverBy::cssSelector('#filter-ctrl-countries > button'))->click();
        $client->waitForVisibility('input[type=checkbox][value=CZ]', 5);

        $client->findElement(WebDriverBy::xpath('//legend[contains(text(), "Europe")]//a[@data-action="all"]'))->click();
        $client->findElement(WebDriverBy::xpath('//button[text() = "Apply"]'))->click();
        $client->waitFor('//div[@id="artisans_info"]/p[contains(text(), "Displaying 2 out of 3 fursuit makers in the database.")]', 5);

        $client->findElement(WebDriverBy::xpath('//td[contains(text(), "Test artisan 1")]'))->click();
        $client->waitForVisibility('//a[@id="makerId" and @href="#TEST001"]', 5);

        self::assertStringContainsString('Test artisan 1', $client->getCrawler()->findElement(WebDriverBy::id('artisanName'))->getText());

        $this->aggressivelyPunchTheKeyboardMultipleTimesWhileShouting_WORK_YOU_PIECE_OF_SHIT_atTheScreen($client);

        $client->findElement(WebDriverBy::xpath('//div[@id="artisanDetailsModalContent"]//button[text() = "Data outdated/inaccurate?"]'))->click();

        $client->waitForVisibility('#artisanUpdatesModalContent', 5);
        self::assertStringContainsString('Test artisan 1', $client->getCrawler()->findElement(WebDriverBy::id('updateRequestLabel'))->getText());

        $this->aggressivelyPunchTheKeyboardMultipleTimesWhileShouting_WORK_YOU_PIECE_OF_SHIT_atTheScreen($client);

        $client->findElement(WebDriverBy::cssSelector('#artisanUpdatesModalContent .modal-footer > button'))->click();
        $client->waitForInvisibility('#artisanUpdatesModalContent', 5);

        $client->findElement(WebDriverBy::cssSelector('#TEST003 td.links div.btn-group > button'))->click();
        $client->waitForVisibility('#TEST003 td.links div.btn-group > ul li:last-child > a', 5);

        $client->findElement(WebDriverBy::cssSelector('#TEST003 td.links div.btn-group > ul li:last-child > a'))->click();
        $client->waitForVisibility('#artisanUpdatesModalContent', 5);

        self::assertStringContainsString('Test artisan 3', $client->getCrawler()->findElement(WebDriverBy::id('updateRequestLabel'))->getText());
    }

    /**
     * If only I was competent enough to be able to fix this test properly.
     *
     * @throws Exception
     */
    private function aggressivelyPunchTheKeyboardMultipleTimesWhileShouting_WORK_YOU_PIECE_OF_SHIT_atTheScreen(Client $client): void
    {
        $client->getKeyboard()->pressKey(WebDriverKeys::PAGE_DOWN);
        usleep(100000);

        $client->getKeyboard()->pressKey(WebDriverKeys::PAGE_DOWN);
        usleep(100000);
    }
}
