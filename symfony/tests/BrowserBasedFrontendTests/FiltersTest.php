<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Tests\BrowserBasedFrontendTests\Traits\MainPageTestsTrait;
use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use App\Tests\TestUtils\Cases\Traits\FiltersTestTrait;
use App\Tests\TestUtils\FiltersData;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use JsonException;

/**
 * @large
 */
class FiltersTest extends PantherTestCaseWithEM
{
    use FiltersTestTrait;
    use MainPageTestsTrait;

    /**
     * @dataProvider filterChoicesDataProvider
     *
     * @param list<Artisan>                    $artisans
     * @param array<string, list<string>|bool> $filtersSet
     * @param list<string>                     $expectedMakerIds
     *
     * @throws WebDriverException|JsonException
     */
    public function testFiltersInBrowser(array $artisans, array $filtersSet, array $expectedMakerIds): void
    {
        self::persistAndFlush(...$artisans, ...FiltersData::entitiesFrom($artisans));
        $this->clearCache();

        $this->client->request('GET', '/index.php/');

        $isAdult = (bool) ($filtersSet['isAdult'] ?? true);
        $wantsSfw = (bool) ($filtersSet['wantsSfw'] ?? false);

        self::fillChecklist($this->client, $isAdult, $wantsSfw);

        $this->client->findElement(WebDriverBy::id('open-filters-button'))->click();
        self::waitUntilShows('#filters-title');

        foreach ($filtersSet as $filter => $values) {
            if (is_bool($values)) {
                continue;
            }

            $this->client->findElement(WebDriverBy::cssSelector("#filter-ctrl-$filter > button"))->click();
            self::waitUntilShows("#filter-body-$filter");

            if ('species' === $filter) {
                $this->toggleSpecies('Most species');
            }

            foreach ($values as $value) {
                $this->client->findElement(WebDriverBy::xpath("//input[@name=\"{$filter}[]\"][@value=\"$value\"]"))->click();
            }
        }

        $this->client->findElement(WebDriverBy::xpath('//button[normalize-space(text()) = "Apply"]'))->click();
        self::waitUntilHides('#filters-title', 1000);
        self::waitForLoadingIndicatorToDisappear();

        self::assertSelectorTextContains('#artisans-table-count', 'Displaying '.count($expectedMakerIds).' out of');

        foreach ($expectedMakerIds as $makerId) {
            self::assertSelectorIsVisible("tr#$makerId");
        }
    }

    /**
     * @throws WebDriverException
     */
    private function toggleSpecies(string ...$specieNames): void
    {
        foreach ($specieNames as $specieName) {
            $xpath = '//input[@value="'.$specieName.'"]/ancestor::div[@role="group"]/span[contains(@class, "toggle")]';
            $this->client->findElement(WebDriverBy::xpath($xpath))->click();

            $xpath = '//input[@value="'.$specieName.'"]/ancestor::div[@role="group"]/following-sibling::fieldset';
            self::waitUntilShows($xpath);
        }
    }
}
