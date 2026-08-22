<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;

trait MainPageTestsTrait
{
    /**
     * @throws WebDriverException
     */
    private function loadMainPage(int $expectedNumberOfCreators, int $expectedNumberOfCountries): void
    {
        self::$client->request('GET', '/index.php/');

        $infoText = "Currently $expectedNumberOfCreators makers/studios from $expectedNumberOfCountries countries are listed here.";
        self::$client->waitForElementToContain('.alert-dismissible p:not(.intro-updated-info)', $infoText, 5);
    }

    /**
     * @throws WebDriverException
     */
    private function skipCheckListAdultAllowNsfw(int $expectedNumberOfCreators, bool $expectFilled = false): void
    {
        $this->fillChecklist(true, false, $expectFilled);
        $this->waitExpectLoadedCreatorsTable($expectedNumberOfCreators, $expectedNumberOfCreators); // Assumes no paging happening
    }

    /**
     * @throws WebDriverException
     */
    private function waitExpectLoadedCreatorsTable(int $displaying, int $outOf): void
    {
        $locator = "//div[@id=\"main-creators-pagination\"]/p[contains(text(), \"Displaying $displaying out of $outOf matched fursuit makers.\")]";

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
    private function waitForLoadingIndicatorToDisappear(int $millisecondsForAnimation = 500): void
    {
        self::waitUntilHides('#loading-indicator', $millisecondsForAnimation);
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
    private function closeCreatorCardUpByClickingTheCross(): void
    {
        self::$client->findElement(WebDriverBy::cssSelector('#creator-card-modal .modal-header > button'))->click();
        self::$client->waitForInvisibility('#creator-card-modal', 5);
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
