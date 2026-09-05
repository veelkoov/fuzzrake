<?php

declare(strict_types=1);

namespace App\Tests\BrowserBased;

use App\Tests\TestUtils\Cases\FuzzrakePantherTestCase;
use App\Tests\TestUtils\Cases\Traits\MainPageTestsTrait;
use App\Tests\TestUtils\UserCreator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Large;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

#[Large]
class MainPageTest extends FuzzrakePantherTestCase
{
    use MainPageTestsTrait;
    use ClockSensitiveTrait;

    /**
     * @throws Exception
     */
    public function testMainPageUiSmoke(): void
    {
        self::persistAndFlush(
            UserCreator::get(true)->setCreatorId('TEST001')->setName('Test creator 1 CZ')->setCountry('CZ'),
            UserCreator::get(true)->setCreatorId('TEST002')->setName('Test creator 2 CA')->setCountry('CA'),
            UserCreator::get(true)->setCreatorId('TEST003')->setName('Test creator 3 DE')->setCountry('DE'),
        );

        $this->clearCache();

        $this->loadMainPage(3, 3);
        $this->skipCheckListAdultAllowNsfw(3);

        $this->openFiltersPopUp();
        $this->openCountriesFilter();

        $this->selectCountryInFilters('CZ');
        $this->assertCountriesFilterSelections(['CZ'], ['DE', 'CA']);

        // Click "invert" on Europe
        self::$client->findElement(WebDriverBy::xpath('//legend[contains(text(), "Europe")]//a[text() = "invert"]'))->click();
        $this->assertCountriesFilterSelections(['DE'], ['CZ', 'CA']);

        // Click "none" on Europe
        self::$client->findElement(WebDriverBy::xpath('//legend[contains(text(), "Europe")]//a[text() = "none"]'))->click();
        $this->assertCountriesFilterSelections([], ['CZ', 'DE', 'CA']);

        // Click "all" on Europe
        self::$client->findElement(WebDriverBy::xpath('//legend[contains(text(), "Europe")]//a[text() = "all"]'))->click();
        $this->assertCountriesFilterSelections(['CZ', 'DE'], ['CA']);

        $this->clickApplyInTheFiltersPopUp();

        $this->waitExpectLoadedCreatorsTable(2, 2);

        $creatorIdLocator = '#TEST001.creator-card span.creator-id';
        self::assertSelectorIsNotVisible($creatorIdLocator);
        $this->openCreatorCardByClickingOnTheHeader('TEST001');
        self::assertSelectorIsVisible($creatorIdLocator);

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
     * @throws DateTimeException
     * @throws WebDriverException
     */
    public function testNewlyAddedIndicators(): void
    {
        self::mockTime();

        $creator1 = UserCreator::get(true)->setCreatorId('TEST001')->setName('Older creator')->setCountry('FI')->setDateAdded(UtcClock::at('-43 days'));
        $creator2 = UserCreator::get(true)->setCreatorId('TEST002')->setName('Newer creator 1')->setCountry('CZ')->setDateAdded(UtcClock::at('-41 days'));

        self::persistAndFlush($creator1, $creator2);
        $this->clearCache();

        $this->loadMainPage(2, 2);
        $this->skipCheckListAdultAllowNsfw(2);

        self::assertSelectorExists('#TEST002 span.new-creator');
        self::assertSelectorExists('#TEST001');
        self::assertSelectorNotExists('#TEST001 span.new-creator');
    }

    /**
     * @throws WebDriverException
     */
    #[DataProvider('openingCreatorCardByCreatorIdDataProvider')]
    public function testOpeningCreatorCardByCreatorId(string $path): void
    {
        self::persistAndFlush(UserCreator::get(true)
            ->setName('Opening standalone card')
            ->setCreatorId('TEST001')
            ->setInactiveReason('Testing')); // Must show up even if deactivated
        $this->clearCache();

        self::$client->request('GET', $path);

        self::assertSelectorTextSame('.creator-card h5.name', 'Opening standalone card');
        self::assertSelectorTextContains('.creator-card .creator-id', 'TEST001');
    }

    public static function openingCreatorCardByCreatorIdDataProvider(): iterable
    {
        return [['/#TEST001'], ['/c/TEST001']];
    }

    /**
     * @throws WebDriverException
     */
    public function testFilterChoicesGetSavedAndRestored(): void
    {
        self::persistAndFlush(UserCreator::get(true)->setCountry('FI'));
        $this->clearCache();

        $this->loadMainPage(1, 1);
        $this->skipCheckListAdultAllowNsfw(1);

        $this->openFiltersPopUp();
        $this->openCountriesFilter();

        $this->selectCountryInFilters('FI');
        $this->selectCountryInFilters('?');
        $this->assertCountriesFilterSelections(['FI', '?'], []);
        $this->clickApplyInTheFiltersPopUp();

        $this->waitExpectLoadedCreatorsTable(1, 1);

        usleep(500_000); // Lame grep-code-dumb-workarounds-in-tests
        $this->loadMainPage(1, 1);
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
        self::persistAndFlush(UserCreator::get(true)
            ->setCreatorId('TEST001')
            ->setCountry('FI')
            ->setStyles(['Toony']));

        $this->loadMainPage(1, 1);
        $this->skipCheckListAdultAllowNsfw(1);

        $creatorIdSelector = '//div[@id="TEST001"]//span[contains(@class, "creator-id") and contains(., "TEST001")]';
        $stylesSelector = '//div[@id="TEST001"]//div[contains(@class, "styles") and contains(., "Toony")]';

        // Check the defaults: styles are visible, creator IDs are hidden
        self::assertSelectorIsVisible($stylesSelector);
        self::assertSelectorIsNotVisible($creatorIdSelector);

        // Show creator ID, hide styles
        self::$client->findElement(WebDriverBy::xpath('//button[normalize-space(text()) = "Preferences"]'))->click();
        self::$client->findElement(WebDriverBy::id('checkbox-creator-id'))->click();
        self::$client->findElement(WebDriverBy::id('checkbox-styles'))->click();

        // Check if the change has been applied
        self::assertSelectorIsNotVisible($stylesSelector);
        self::assertSelectorIsVisible($creatorIdSelector);

        // Reload the page
        $this->loadMainPage(1, 1);
        $this->skipCheckListAdultAllowNsfw(1, true);

        // Check if the change has persisted between page loads
        self::assertSelectorIsNotVisible($stylesSelector);
        self::assertSelectorIsVisible($creatorIdSelector);
    }

    /**
     * @throws WebDriverException
     */
    private function openCountriesFilter(): void
    {
        self::$client->findElement(WebDriverBy::cssSelector('#filter-ctrl-countries > button'))->click();
        self::waitUntilShows('input[type=checkbox][name="countries[]"]', 1000);
    }

    /**
     * @throws NoSuchElementException
     */
    private function selectCountryInFilters(string $countryCode): void
    {
        $selector = "input[type=checkbox][name='countries[]'][value='$countryCode']";

        self::$client->findElement(WebDriverBy::cssSelector($selector))->click();
    }

    /**
     * @throws WebDriverException
     */
    private function openFiltersPopUp(): void
    {
        self::$client->findElement(WebDriverBy::id('open-filters-button'))->click();
        self::$client->waitForVisibility('#filters-title', 5);
    }

    /**
     * @throws WebDriverException
     */
    private function clickApplyInTheFiltersPopUp(): void
    {
        self::$client->findElement(WebDriverBy::xpath('//button[normalize-space(text()) = "Apply"]'))->click();

        self::waitForLoadingIndicatorToDisappear();
    }
}
