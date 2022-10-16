<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests\Traits;

use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\Client;

trait MainPageTestsTrait
{
    /**
     * @throws WebDriverException
     */
    private static function skipCheckListAdultAllowNsfw(Client $client, int $numberOfArtisans): void
    {
        $infoText = "Currently $numberOfArtisans makers from $numberOfArtisans countries are listed here.";
        $client->waitForElementToContain('.alert-dismissible p:not(.intro-updated-info)', $infoText, 5);

        $client->findElement(WebDriverBy::id('checklist-ill-be-careful'))->click();

        self::waitUntilShows('#aasImAdult');
        $client->findElement(WebDriverBy::id('aasImAdult'))->click();

        self::waitUntilShows('#aasAllowNsfw');
        $client->findElement(WebDriverBy::id('aasAllowNsfw'))->click();

        self::waitUntilShows('#checklist-dismiss-btn');
        $client->findElement(WebDriverBy::id('checklist-dismiss-btn'))->click();

        $client->waitForVisibility('#artisans', 5);

        self::assertStringContainsString("Displaying $numberOfArtisans out of $numberOfArtisans fursuit makers in the database.", $client->getCrawler()->findElement(WebDriverBy::id('artisans_info'))->getText());
    }

    /**
     * @throws WebDriverException
     */
    private static function openMakerCardByClickingOnTheirNameInTheTable(Client $client, string $makerName): void
    {
        $client->findElement(WebDriverBy::xpath('//td[contains(text(), "'.$makerName.'")]'))->click();

        self::waitUntilShows('#artisanName');
        self::assertSelectorTextSame('#artisanName', $makerName);
    }

    /**
     * @throws WebDriverException
     */
    private static function openDataOutdatedPopup(Client $client): void
    {
        $reportButtonXpath = '//div[@id="artisanDetailsModalContent"]//button[text() = "Data outdated/inaccurate?"]';

        $client->findElement(WebDriverBy::xpath($reportButtonXpath))->click();
        $client->waitForVisibility('#artisanUpdatesModalContent', 5);
    }
}
