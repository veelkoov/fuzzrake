<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests\Traits;

use App\Tests\TestUtils\FiltersData;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\Client;

trait MainPageTestsTrait
{
    /**
     * @throws WebDriverException
     */
    private static function skipCheckListAdultAllowNsfw(Client $client, int $numberOfArtisans, bool $expectFilled = false): void
    {
        $infoText = "Currently $numberOfArtisans makers from $numberOfArtisans countries are listed here.";
        $client->waitForElementToContain('.alert-dismissible p:not(.intro-updated-info)', $infoText, 5);

        self::fillChecklist($client, true, false, $expectFilled);

        self::assertStringContainsString("Displaying $numberOfArtisans out of $numberOfArtisans fursuit makers in the database.", $client->getCrawler()->findElement(WebDriverBy::id('artisans-table-count'))->getText());
    }

    /**
     * @throws WebDriverException
     */
    private static function fillChecklist(Client $client, bool $isAdult, bool $wantsSfw, bool $expectFilled = false): void
    {
        self::waitForLoadingIndicatorToDisappear();

        if (!$expectFilled) {
            $client->findElement(WebDriverBy::id('checklist-ill-be-careful'))->click();

            if ($isAdult) {
                self::waitUntilShows('#aasImAdult');
                $client->findElement(WebDriverBy::id('aasImAdult'))->click();

                if ($wantsSfw) {
                    self::waitUntilShows('#aasKeepSfw');
                    $client->findElement(WebDriverBy::id('aasKeepSfw'))->click();
                } else {
                    self::waitUntilShows('#aasAllowNsfw');
                    $client->findElement(WebDriverBy::id('aasAllowNsfw'))->click();
                }
            } else {
                self::waitUntilShows('#aasImNotAdult');
                $client->findElement(WebDriverBy::id('aasImNotAdult'))->click();
            }
        }

        self::waitUntilShows('#checklist-dismiss-btn');
        $client->findElement(WebDriverBy::id('checklist-dismiss-btn'))->click();

        self::waitForLoadingIndicatorToDisappear();
    }

    /**
     * @throws WebDriverException
     */
    private static function waitForLoadingIndicatorToDisappear(bool $checkIfShowsUp = false): void
    {
        if ($checkIfShowsUp) {
            self::waitUntilShows('#loading-indicator', 0);
        }

        self::waitUntilHides('#loading-indicator');
    }

    /**
     * @throws WebDriverException
     */
    private static function openMakerCardByClickingOnTheirNameInTheTable(Client $client, string $makerName): void
    {
        $client->findElement(WebDriverBy::xpath('//td[contains(., "'.$makerName.'")]'))->click();

        self::waitUntilShows('#artisanName');
        self::assertSelectorTextSame('#artisanName', $makerName);
    }

    /**
     * @throws WebDriverException
     */
    private static function openDataOutdatedPopupFromTheMakerCard(Client $client): void
    {
        $reportButtonXpath = '//div[@id="creator-card-modal-content"]//button[normalize-space(text()) = "Data outdated/inaccurate?"]';

        $client->findElement(WebDriverBy::xpath($reportButtonXpath))->click();
        $client->waitForVisibility('#creator-updates-modal-content', 5);
    }

    /**
     * @throws WebDriverException
     */
    private static function closeDataOutdatedPopUpByClickingTheCloseButton(Client $client): void
    {
        $client->findElement(WebDriverBy::cssSelector('#creator-updates-modal-content .modal-footer > button'))->click();
        $client->waitForInvisibility('#creator-updates-modal-content', 5);
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
