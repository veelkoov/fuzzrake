<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests\Traits;

use App\Tests\TestUtils\FiltersData;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;

trait MainPageTestsTrait
{
    /**
     * @throws WebDriverException
     */
    private function skipCheckListAdultAllowNsfw(int $numberOfArtisans, bool $expectFilled = false): void
    {
        $infoText = "Currently $numberOfArtisans makers from $numberOfArtisans countries are listed here.";
        $this->client->waitForElementToContain('.alert-dismissible p:not(.intro-updated-info)', $infoText, 5);

        $this->fillChecklist(true, false, $expectFilled);
        $this->waitExpectLoadedCreatorsTable($numberOfArtisans, $numberOfArtisans); // Assumes no paging happening
    }

    /**
     * @throws WebDriverException
     */
    private function waitExpectLoadedCreatorsTable(int $displaying, int $outOf): void
    {
        $locator = "//div[@id=\"creators-table-pagination\"]/p[contains(text(), \"Displaying $displaying out of $outOf matched fursuit makers.\")]";

        $this->client->waitFor($locator, 3);
    }

    /**
     * @throws WebDriverException
     */
    private function fillChecklist(bool $isAdult, bool $wantsSfw, bool $expectFilled = false): void
    {
        try {
            self::waitForLoadingIndicatorToDisappear();

            if (!$expectFilled) {
                $this->client->findElement(WebDriverBy::id('checklist-ill-be-careful'))->click();

                if ($isAdult) {
                    self::waitUntilShows('#aasImAdult');
                    $this->client->findElement(WebDriverBy::id('aasImAdult'))->click();

                    if ($wantsSfw) {
                        self::waitUntilShows('#aasKeepSfw');
                        $this->client->findElement(WebDriverBy::id('aasKeepSfw'))->click();
                    } else {
                        self::waitUntilShows('#aasAllowNsfw');
                        $this->client->findElement(WebDriverBy::id('aasAllowNsfw'))->click();
                    }
                } else {
                    self::waitUntilShows('#aasImNotAdult');
                    $this->client->findElement(WebDriverBy::id('aasImNotAdult'))->click();
                }
            }

            self::waitUntilShows('#checklist-dismiss-btn');
            $this->client->findElement(WebDriverBy::id('checklist-dismiss-btn'))->click();

            self::waitForLoadingIndicatorToDisappear();
        } catch (NoSuchElementException) {
            echo $this->client->getCrawler()->html();
        }
    }

    /**
     * @throws WebDriverException
     */
    private function waitForLoadingIndicatorToDisappear(): void
    {
        self::waitUntilHides('#loading-indicator', 550);
    }

    /**
     * @throws WebDriverException
     */
    private function openMakerCardByClickingOnTheirNameInTheTable(string $makerName): void
    {
        $this->client->findElement(WebDriverBy::xpath('//td[contains(., "'.$makerName.'")]'))->click();

        self::waitUntilShows('#artisanName');
        self::assertSelectorTextSame('#artisanName', $makerName);
    }

    /**
     * @throws WebDriverException
     */
    private function openDataOutdatedPopupFromTheMakerCard(): void
    {
        $reportButtonXpath = '//div[@id="creator-card-modal-content"]//button[normalize-space(text()) = "Data outdated/inaccurate?"]';

        $this->client->findElement(WebDriverBy::xpath($reportButtonXpath))->click();
        $this->client->waitForVisibility('#creator-updates-modal-content', 5);
    }

    /**
     * @throws WebDriverException
     */
    private function closeDataOutdatedPopUpByClickingTheCloseButton(): void
    {
        $this->client->findElement(WebDriverBy::cssSelector('#creator-updates-modal-content .modal-footer > button'))->click();
        $this->client->waitForInvisibility('#creator-updates-modal-content', 5);
    }

    private static function setupMockSpeciesFilterData(): void
    {
        self::persistAndFlush(FiltersData::getMockSpecies());
    }

    /**
     * @param list<string> $visibleCreatorIds
     * @param list<string> $hiddenCreatorIds
     */
    private function assertMakersVisibility(array $visibleCreatorIds, array $hiddenCreatorIds): void
    {
        foreach ($visibleCreatorIds as $creatorId) {
            self::assertSelectorIsVisible("#$creatorId");
        }

        foreach ($hiddenCreatorIds as $creatorId) {
            self::assertSelectorNotExists("#$creatorId", "#$creatorId exists");
        }
    }

    /**
     * @throws NoSuchElementException
     */
    private function clearTypeInTextSearch(string $searchedText): void
    {
        $this->client->findElement(WebDriverBy::id('search-text-field'))->clear()->sendKeys($searchedText);
    }
}
