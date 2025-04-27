<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Tests\BrowserBasedFrontendTests\Traits\MainPageTestsTrait;
use App\Tests\TestUtils\Cases\FuzzrakePantherTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\TestUtils\UtcClockMock;
use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;

/**
 * @large
 */
class MainPageTest extends FuzzrakePantherTestCase
{
    use MainPageTestsTrait;

    /**
     * @throws Exception
     */
    public function testMainPageUiSmoke(): void
    {
        self::persistAndFlush(
            self::getCreator('Test creator 1 CZ', 'TEST001', 'CZ'),
            self::getCreator('Test creator 2 CA', 'TEST002', 'CA'),
            self::getCreator('Test creator 3 DE', 'TEST003', 'DE'),
        );

        $this->clearCache();

        $this->client->request('GET', '/index.php/');
        $this->skipCheckListAdultAllowNsfw(3);

        $this->openFiltersPopUp();
        $this->openCountriesFilter();

        $this->selectCountryInFilters('CZ');
        $this->assertCountriesFilterSelections(['CZ'], ['DE', 'CA']);

        // Click "invert" on Europe
        $this->client->findElement(WebDriverBy::xpath('//legend[contains(text(), "Europe")]//a[text() = "invert"]'))->click();
        $this->assertCountriesFilterSelections(['DE'], ['CZ', 'CA']);

        // Click "none" on Europe
        $this->client->findElement(WebDriverBy::xpath('//legend[contains(text(), "Europe")]//a[text() = "none"]'))->click();
        $this->assertCountriesFilterSelections([], ['CZ', 'DE', 'CA']);

        // Click "all" on Europe
        $this->client->findElement(WebDriverBy::xpath('//legend[contains(text(), "Europe")]//a[text() = "all"]'))->click();
        $this->assertCountriesFilterSelections(['CZ', 'DE'], ['CA']);

        $this->clickApplyInTheFiltersPopUp();

        $this->waitExpectLoadedCreatorsTable(2, 2);

        $this->openCreatorCardByClickingOnTheirNameInTheTable('Test creator 1 CZ');
        self::assertSelectorIsVisible('//a[@id="creator-id" and @href="#TEST001"]');

        $this->aggressivelyPunchTheKeyboardMultipleTimesWhileShouting_WORK_YOU_PIECE_OF_SHIT_atTheScreen();

        $this->openDataOutdatedPopupFromTheCreatorCard();
        self::assertStringContainsString('Test creator 1 CZ', $this->client->getCrawler()->findElement(WebDriverBy::id('updateRequestLabel'))->getText());

        $this->aggressivelyPunchTheKeyboardMultipleTimesWhileShouting_WORK_YOU_PIECE_OF_SHIT_atTheScreen();

        $this->closeDataOutdatedPopUpByClickingTheCloseButton();

        // Open the links dropdown
        $this->client->findElement(WebDriverBy::cssSelector('#TEST003 td.links div.btn-group > button'))->click();
        $this->client->waitForVisibility('#TEST003 td.links div.btn-group > ul li:last-child > a', 5);

        // Click the last link - data outdated
        $this->client->findElement(WebDriverBy::cssSelector('#TEST003 td.links div.btn-group > ul li:last-child > a'))->click();
        self::waitUntilShows('#creator-updates-modal-content');
        self::assertStringContainsString('Test creator 3 DE', $this->client->getCrawler()->findElement(WebDriverBy::id('updateRequestLabel'))->getText());

        $this->aggressivelyPunchTheKeyboardMultipleTimesWhileShouting_WORK_YOU_PIECE_OF_SHIT_atTheScreen();

        $this->closeDataOutdatedPopUpByClickingTheCloseButton();

        // Check if text search works
        $this->clearTypeInTextSearch('CZ');
        self::waitForLoadingIndicatorToDisappear();
        $this->assertCreatorsVisibility(['TEST001'], ['TEST002', 'TEST003']);
        $this->clearTypeInTextSearch('DE');
        self::waitForLoadingIndicatorToDisappear();
        $this->assertCreatorsVisibility(['TEST003'], ['TEST001', 'TEST002']);
    }

    /**
     * @param list<string> $selected
     * @param list<string> $notSelected
     */
    private function assertCountriesFilterSelections(array $selected, array $notSelected): void
    {
        foreach ($selected as $country) {
            self::assertSelectorExists("input[type=checkbox][value='$country']:checked");
        }

        foreach ($notSelected as $country) {
            self::assertSelectorExists("input[type=checkbox][value='$country']:not(:checked)");
        }
    }

    /**
     * If only I was competent enough to be able to fix this test properly.
     *
     * @throws Exception
     */
    private function aggressivelyPunchTheKeyboardMultipleTimesWhileShouting_WORK_YOU_PIECE_OF_SHIT_atTheScreen(): void
    {
        $this->client->getKeyboard()->pressKey(WebDriverKeys::PAGE_DOWN);
        usleep(100000);

        $this->client->getKeyboard()->pressKey(WebDriverKeys::PAGE_DOWN);
        usleep(100000);
    }

    /**
     * @throws DateTimeException
     * @throws WebDriverException
     */
    public function testNewlyAddedIndicators(): void
    {
        UtcClockMock::start();

        $creator1 = Creator::new()->setCreatorId('TEST001')->setName('Older creator')->setCountry('FI')->setDateAdded(UtcClock::at('-43 days'));
        $creator2 = Creator::new()->setCreatorId('TEST002')->setName('Newer creator 1')->setCountry('CZ')->setDateAdded(UtcClock::at('-41 days'));

        self::persistAndFlush($creator1, $creator2);
        $this->clearCache();

        $this->client->request('GET', '/index.php/');
        $this->skipCheckListAdultAllowNsfw(2);

        self::assertSelectorExists('#TEST002 span.new-creator');
        self::assertSelectorExists('#TEST001');
        self::assertSelectorNotExists('#TEST001 span.new-creator');
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testOpeningCreatorCardByCreatorId(): void
    {
        $creator = self::getCreator(creatorId: 'TEST001');
        $creator->setInactiveReason('Testing'); // Must show up even if deactivated
        self::persistAndFlush($creator);
        $this->clearCache();

        $this->client->request('GET', '/index.php/#TEST001');

        self::waitUntilShows('#creator-card-modal #creator-id', 1000);
        self::assertSelectorTextSame('#creator-card-modal #creator-id', 'TEST001');
        $this->client->findElement(WebDriverBy::cssSelector('#creator-card-modal-content .modal-header button'))->click();
        self::waitUntilHides('#creator-card-modal #creator-id');
    }

    /**
     * @throws WebDriverException
     */
    public function testFilterChoicesGetSavedAndRestored(): void
    {
        self::persistAndFlush(self::getCreator(country: 'FI'));
        $this->clearCache();

        $this->client->request('GET', '/index.php/');
        $this->skipCheckListAdultAllowNsfw(1);

        $this->openFiltersPopUp();
        $this->openCountriesFilter();

        $this->selectCountryInFilters('FI');
        $this->selectCountryInFilters('?');
        $this->assertCountriesFilterSelections(['FI', '?'], []);
        $this->clickApplyInTheFiltersPopUp();

        $this->waitExpectLoadedCreatorsTable(1, 1);

        usleep(500_000); // Lame
        $this->client->request('GET', '/index.php/');
        $this->skipCheckListAdultAllowNsfw(1, true);

        $this->openFiltersPopUp();
        $this->openCountriesFilter();
        $this->assertCountriesFilterSelections(['FI', '?'], []);
    }

    /**
     * @throws WebDriverException
     */
    public function testColumnVisibilityGetSavedAndRestored(): void
    {
        self::persistAndFlush(self::getCreator(creatorId: 'TEST001', country: 'FI')->setStyles(['Toony']));

        $this->client->request('GET', '/index.php/');
        $this->skipCheckListAdultAllowNsfw(1);

        // Check the defaults: styles are visible, creator IDs are hidden
        self::assertSelectorIsVisible('//td[contains(., "Toony")]');
        self::assertSelectorIsNotVisible('//td[contains(., "TEST001")]');

        // Show creator ID column, hide styles column
        $this->client->findElement(WebDriverBy::xpath('//button[normalize-space(text()) = "Columns"]'))->click();
        $this->client->findElement(WebDriverBy::linkText('Maker ID'))->click();
        $this->client->findElement(WebDriverBy::linkText('Styles'))->click();

        // Check if the change has been applied
        self::assertSelectorIsNotVisible('//td[contains(., "Toony")]');
        self::assertSelectorIsVisible('//td[contains(., "TEST001")]');

        // Reload the page
        $this->client->request('GET', '/index.php/');
        $this->skipCheckListAdultAllowNsfw(1, true);

        // Check if the change has persisted between page loads
        self::assertSelectorIsNotVisible('//td[contains(., "Toony")]');
        self::assertSelectorIsVisible('//td[contains(., "TEST001")]');
    }

    /**
     * @throws WebDriverException
     */
    private function openCountriesFilter(): void
    {
        $this->client->findElement(WebDriverBy::cssSelector('#filter-ctrl-countries > button'))->click();
        self::waitUntilShows('input[type=checkbox][name="countries[]"]', 1000);
    }

    /**
     * @throws NoSuchElementException
     */
    private function selectCountryInFilters(string $countryCode): void
    {
        $selector = "input[type=checkbox][name='countries[]'][value='$countryCode']";

        $this->client->findElement(WebDriverBy::cssSelector($selector))->click();
    }

    /**
     * @throws WebDriverException
     */
    private function openFiltersPopUp(): void
    {
        $this->client->findElement(WebDriverBy::id('open-filters-button'))->click();
        $this->client->waitForVisibility('#filters-title', 5);
    }

    /**
     * @throws WebDriverException
     */
    private function clickApplyInTheFiltersPopUp(): void
    {
        $this->client->findElement(WebDriverBy::xpath('//button[normalize-space(text()) = "Apply"]'))->click();

        self::waitForLoadingIndicatorToDisappear();
    }
}
