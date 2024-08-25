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
use Symfony\Component\Panther\Client;

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
        $client = static::createPantherClient();
        self::setWindowSize($client, 1600, 900);

        self::persistAndFlush(...$artisans, ...FiltersData::entitiesFrom($artisans));
        $this->clearCache();

        $client->request('GET', '/index.php/');

        $isAdult = (bool) ($filtersSet['isAdult'] ?? true);
        $wantsSfw = (bool) ($filtersSet['wantsSfw'] ?? false);

        self::fillChecklist($client, $isAdult, $wantsSfw);

        $client->findElement(WebDriverBy::id('filtersButton'))->click();
        self::waitUntilShows('#filters-title');

        foreach ($filtersSet as $filter => $values) {
            if (is_bool($values)) {
                continue;
            }

            $client->findElement(WebDriverBy::cssSelector("#filter-ctrl-$filter > button"))->click();
            self::waitUntilShows("#filter-body-$filter");

            if ('species' === $filter) {
                $this->toggleSpecies($client, 'Most species');
            }

            foreach ($values as $value) {
                $client->findElement(WebDriverBy::xpath("//input[@name=\"{$filter}[]\"][@value=\"$value\"]"))->click();
            }
        }

        $client->findElement(WebDriverBy::xpath('//button[normalize-space(text()) = "Apply"]'))->click();
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
    private function toggleSpecies(Client $client, string ...$specieNames): void
    {
        foreach ($specieNames as $specieName) {
            $xpath = '//input[@value="'.$specieName.'"]/ancestor::div[@role="group"]/span[contains(@class, "toggle")]';
            $client->findElement(WebDriverBy::xpath($xpath))->click();

            $xpath = '//input[@value="'.$specieName.'"]/ancestor::div[@role="group"]/following-sibling::fieldset';
            self::waitUntilShows($xpath);
        }
    }
}
