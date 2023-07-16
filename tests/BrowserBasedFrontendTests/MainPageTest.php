<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Tests\BrowserBasedFrontendTests\Traits\MainPageTestsTrait;
use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\TestUtils\UtcClockMock;
use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Symfony\Component\Panther\Client;

/**
 * @large
 */
class MainPageTest extends PantherTestCaseWithEM
{
    use MainPageTestsTrait;

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

        $this->clearCache();

        $client->request('GET', '/index.php/');
        self::skipCheckListAdultAllowNsfw($client, 3);

        $client->findElement(WebDriverBy::id('filtersButton'))->click();
        $client->waitForVisibility('#filtersTitle', 5);

        $client->findElement(WebDriverBy::cssSelector('#filter-ctrl-countries > button'))->click();
        $client->waitForVisibility('input[type=checkbox][value=CZ]', 5);

        $client->findElement(WebDriverBy::xpath('//legend[contains(text(), "Europe")]//a[text() = "all"]'))->click();
        $client->findElement(WebDriverBy::xpath('//button[text() = "Apply"]'))->click();
        self::waitForLoadingIndicatorToDisappear();
        $client->waitFor('//p[@id="artisans-table-count" and contains(text(), "Displaying 2 out of 3 fursuit makers in the database.")]', 1);

        self::openMakerCardByClickingOnTheirNameInTheTable($client, 'Test artisan 1');
        self::assertSelectorIsVisible('//a[@id="makerId" and @href="#TEST001"]');

        $this->aggressivelyPunchTheKeyboardMultipleTimesWhileShouting_WORK_YOU_PIECE_OF_SHIT_atTheScreen($client);

        self::openDataOutdatedPopup($client);

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

    /**
     * @throws DateTimeException
     * @throws WebDriverException
     */
    public function testNewlyAddedIndicators(): void
    {
        $client = static::createPantherClient();
        self::setWindowSize($client, 1600, 900);
        UtcClockMock::start();

        $maker1 = Artisan::new()->setMakerId('MAKEOLD')->setName('Older maker')->setCountry('FI')->setDateAdded(UtcClock::at('-43 days'));
        $maker2 = Artisan::new()->setMakerId('MAKENEW')->setName('Newer maker 1')->setCountry('CZ')->setDateAdded(UtcClock::at('-41 days'));

        self::persistAndFlush($maker1, $maker2);
        $this->clearCache();

        $client->request('GET', '/index.php/');
        self::skipCheckListAdultAllowNsfw($client, 2);

        self::assertSelectorExists('#MAKENEW span.new-artisan');
        self::assertSelectorExists('#MAKEOLD');
        self::assertSelectorNotExists('#MAKEOLD span.new-artisan');
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testOpeningArtisanCardByMakerId(): void
    {
        $client = static::createPantherClient();
        self::setWindowSize($client, 1600, 900);

        $artisan = self::getArtisan('Test artisan 1', 'TEST001', 'FI');
        $artisan->setInactiveReason('Testing'); // Must show up even if deactivated
        self::persistAndFlush($artisan);
        $this->clearCache();

        $client->request('GET', '/index.php/#TEST001');

        self::waitUntilShows('#artisanDetailsModal #makerId', 1000);
        self::assertSelectorTextSame('#artisanDetailsModal #makerId', 'TEST001');
        $client->findElement(WebDriverBy::cssSelector('#artisanDetailsModalContent .modal-header button'))->click();
        self::waitUntilHides('#artisanDetailsModal #makerId');
    }
}
