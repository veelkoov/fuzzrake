<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Tests\BrowserBasedFrontendTests\Traits\MainPageTestsTrait;
use App\Tests\TestUtils\Cases\FuzzrakePantherTestCase;
use App\Tests\TestUtils\Cases\Traits\FiltersTestTrait;
use App\Tests\TestUtils\FiltersData;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use JsonException;

/**
 * @large
 */
class FiltersTest extends FuzzrakePantherTestCase
{
    use FiltersTestTrait;
    use MainPageTestsTrait;

    /**
     * @dataProvider filterChoicesDataProvider
     *
     * @param list<Creator>                    $creators
     * @param array<string, list<string>|bool> $filtersSet
     * @param list<string>                     $expectedCreatorIds
     *
     * @throws WebDriverException|JsonException
     */
    public function testFiltersInBrowser(array $creators, array $filtersSet, array $expectedCreatorIds): void
    {
        self::persistAndFlush(...$creators, ...FiltersData::entitiesFrom($creators));
        $this->clearCache();

        self::$client->request('GET', '/index.php/');

        $isAdult = (bool) ($filtersSet['isAdult'] ?? true);
        $wantsSfw = (bool) ($filtersSet['wantsSfw'] ?? false);

        $this->fillChecklist($isAdult, $wantsSfw);

        self::$client->findElement(WebDriverBy::id('open-filters-button'))->click();
        self::waitUntilShows('#filters-title');

        foreach ($filtersSet as $filter => $values) {
            if (is_bool($values)) {
                continue;
            }

            self::$client->findElement(WebDriverBy::cssSelector("#filter-ctrl-$filter > button"))->click();
            self::waitUntilShows("#filter-body-$filter");

            if ('species' === $filter) {
                $this->toggleSpecies('Most species');
            }

            foreach ($values as $value) {
                self::$client->findElement(WebDriverBy::xpath("//input[@name=\"{$filter}[]\"][@value=\"$value\"]"))->click();
            }
        }

        self::$client->findElement(WebDriverBy::xpath('//button[normalize-space(text()) = "Apply"]'))->click();
        self::waitUntilHides('#filters-title', 1000);
        self::waitForLoadingIndicatorToDisappear();

        self::assertSelectorTextContains('#creators-table-pagination', 'Displaying '.count($expectedCreatorIds).' out of');

        foreach ($expectedCreatorIds as $creatorId) {
            self::assertSelectorIsVisible("tr#$creatorId");
        }
    }

    /**
     * @throws WebDriverException
     */
    private function toggleSpecies(string ...$specieNames): void
    {
        foreach ($specieNames as $specieName) {
            $xpath = '//input[@value="'.$specieName.'"]/ancestor::div[@role="group"]/span[contains(@class, "toggle")]';
            self::$client->findElement(WebDriverBy::xpath($xpath))->click();

            $xpath = '//input[@value="'.$specieName.'"]/ancestor::div[@role="group"]/following-sibling::fieldset';
            self::waitUntilShows($xpath);
        }
    }
}
