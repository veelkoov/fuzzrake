<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests\Traits;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;

trait MainPageTestsTrait
{
    /**
     * @throws WebDriverException
     */
    private function skipCheckListAdultAllowNsfw(int $numberOfCreators, bool $expectFilled = false): void
    {
        $infoText = "Currently $numberOfCreators makers from $numberOfCreators countries are listed here.";
        self::$client->waitForElementToContain('.alert-dismissible p:not(.intro-updated-info)', $infoText, 5);

        $this->fillChecklist(true, false, $expectFilled);
        $this->waitExpectLoadedCreatorsTable($numberOfCreators, $numberOfCreators); // Assumes no paging happening
    }

    /**
     * @throws WebDriverException
     */
    private function waitExpectLoadedCreatorsTable(int $displaying, int $outOf): void
    {
        $locator = "//div[@id=\"creators-table-pagination\"]/p[contains(text(), \"Displaying $displaying out of $outOf matched fursuit makers.\")]";

        self::$client->waitFor($locator, 3);
    }

    /**
     * @throws WebDriverException
     */
    private function fillChecklist(bool $isAdult, bool $wantsSfw, bool $expectFilled = false): void
    {
        self::waitForLoadingIndicatorToDisappear();

        if (!$expectFilled) {
            self::$client->findElement(WebDriverBy::id('checklist-ill-be-careful'))->click();

            if ($isAdult) {
                self::waitUntilShows('#aasImAdult');
                self::$client->findElement(WebDriverBy::id('aasImAdult'))->click();

                if ($wantsSfw) {
                    self::waitUntilShows('#aasKeepSfw');
                    self::$client->findElement(WebDriverBy::id('aasKeepSfw'))->click();
                } else {
                    self::waitUntilShows('#aasAllowNsfw');
                    self::$client->findElement(WebDriverBy::id('aasAllowNsfw'))->click();
                }
            } else {
                self::waitUntilShows('#aasImNotAdult');
                self::$client->findElement(WebDriverBy::id('aasImNotAdult'))->click();
            }
        }

        self::waitUntilShows('#checklist-dismiss-btn');
        self::$client->findElement(WebDriverBy::id('checklist-dismiss-btn'))->click();

        self::waitForLoadingIndicatorToDisappear();
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
    private function openCreatorCardByClickingOnTheirNameInTheTable(string $creatorName): void
    {
        self::$client->findElement(WebDriverBy::xpath('//td[contains(., "'.$creatorName.'")]'))->click();

        self::waitUntilShows('#creator-name');
        self::assertSelectorTextSame('#creator-name', $creatorName);
    }

    /**
     * @throws WebDriverException
     */
    private function openDataOutdatedPopupFromTheCreatorCard(): void
    {
        $reportButtonXpath = '//div[@id="creator-card-modal-content"]//button[normalize-space(text()) = "Data outdated/inaccurate?"]';

        self::$client->findElement(WebDriverBy::xpath($reportButtonXpath))->click();
        self::waitUntilShows('#creator-updates-modal-content');
    }

    /**
     * @throws WebDriverException
     */
    private function closeDataOutdatedPopUpByClickingTheCloseButton(): void
    {
        self::$client->findElement(WebDriverBy::cssSelector('#creator-updates-modal-content .modal-footer > button'))->click();
        self::$client->waitForInvisibility('#creator-updates-modal-content', 5);
    }

    /**
     * @param list<string> $visibleCreatorIds
     * @param list<string> $hiddenCreatorIds
     */
    private function assertCreatorsVisibility(array $visibleCreatorIds, array $hiddenCreatorIds): void
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
        self::$client->findElement(WebDriverBy::id('search-text-field'))->clear()->sendKeys($searchedText);
    }
}
