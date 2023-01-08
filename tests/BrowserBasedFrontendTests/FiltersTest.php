<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Tests\BrowserBasedFrontendTests\Traits\MainPageTestsTrait;
use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use App\Tests\TestUtils\Cases\Traits\FiltersTestTrait;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;

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
     * @param array<string, list<string>|bool> $filtersSet
     * @param list<string>                     $expectedMakerIds
     *
     * @throws WebDriverException
     */
    public function testFiltersInBrowser(array $filtersSet, array $expectedMakerIds): void
    {
        $client = static::createPantherClient();
        self::setWindowSize($client, 1600, 900);

        self::persistAndFlush(...$this->getTestArtisans());
        $this->clearCache();

        $client->request('GET', '/index.php/');

        $isAdult = (bool) ($filtersSet['isAdult'] ?? true);
        $wantsSfw = (bool) ($filtersSet['wantsSfw'] ?? false);

        self::fillChecklist($client, $isAdult, $wantsSfw);

        $client->findElement(WebDriverBy::id('filtersButton'))->click();
        self::waitUntilShows('#filtersTitle');

        foreach ($filtersSet as $filter => $values) {
            if (is_bool($values)) {
                continue;
            }

            $client->findElement(WebDriverBy::cssSelector("#filter-ctrl-$filter > button"))->click();
            self::waitUntilShows("#filter-body-$filter");

            if ('species' === $filter) {
                $client->findElement(WebDriverBy::cssSelector('#filter-body-species > fieldset div[role=group] span.toggle'))->click();
                self::waitUntilShows('#filter-body-species > fieldset fieldset.subspecies');
            }

            foreach ($values as $value) {
                $client->findElement(WebDriverBy::xpath("//input[@name=\"{$filter}[]\"][@value=\"$value\"]"))->click();
            }
        }

        $client->findElement(WebDriverBy::xpath('//button[text() = "Apply"]'))->click();
        self::waitUntilHides('#filtersTitle', 1000);
        self::waitForLoadingIndicatorToDisappear();

        self::assertSelectorTextContains('#artisans-table-count', 'Displaying '.count($expectedMakerIds).' out of');

        foreach ($expectedMakerIds as $makerId) {
            self::assertSelectorIsVisible("tr#$makerId");
        }
    }
}
