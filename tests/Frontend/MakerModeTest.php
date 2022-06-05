<?php

declare(strict_types=1);

namespace App\Tests\Frontend;

use App\DataDefinitions\Ages;
use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use App\Tests\TestUtils\TestCache;
use Exception;
use Facebook\WebDriver\WebDriverBy;

class MakerModeTest extends PantherTestCaseWithEM
{
    /**
     * @throws Exception
     */
    public function testTurningMakerModeOnAndOff(): void
    {
        $client = static::createPantherClient();
        self::setWindowSize($client, 1600, 900);

        // Having two makers, 1 minor-friendly and one NSFW-ish

        self::persistAndFlush(
            self::getArtisan('Maker: adult, NSFW', 'TEST001', 'FI', ages: Ages::ADULTS,
                nsfwWebsite: false, nsfwSocial: true, doesNsfw: true, worksWithMinors: false),
            self::getArtisan('Maker: minor, wwn', 'TEST002', 'FI', ages: Ages::MINORS,
                nsfwWebsite: false, nsfwSocial: false, doesNsfw: false, worksWithMinors: true),
        );

        TestCache::clear();

        // Expect: main page initially shows the checklist and no makers

        $client->request('GET', '/index.php/');

        self::assertVisible('#checklist-ill-be-careful');
        self::assertInvisible('#TEST001');
        self::assertInvisible('#TEST002');
        self::assertInvisible('#btn-reenable-filters');

        // Action: navigate to the data updates page and enable the maker mode, go back to the main page

        $client->request('GET', '/index.php/iu_form/start');

        $client->clickLink('Temporarily disable all the filters and open the main page');
        $client->request('GET', '/index.php/'); // Workaround for the new tab being opened

        // Expect: checklist is hidden and all makers are visible

        self::assertInvisible('#checklist-ill-be-careful');
        self::assertVisible('#TEST001');
        self::assertVisible('#TEST002');
        self::assertVisible('#btn-reenable-filters');

        // Action: click re-enable filters button

        $client->clickLink('Re-enable filters');

        // Expect: main page shows the checklist and no makers, no checklist items are selected

        self::assertVisible('#checklist-ill-be-careful');
        self::assertInvisible('#TEST001');
        self::assertInvisible('#TEST002');
        self::assertInvisible('#btn-reenable-filters');

        self::assertSelectorAttributeContains('#checklist-dismiss-btn', 'value', "I can't click this button yet");

        // Action: fill the checklist, aim for minors-friendly experience

        $client->findElement(WebDriverBy::id('checklist-ill-be-careful'))->click();
        self::waitUntilShows('#aasImNotAdult');
        $client->findElement(WebDriverBy::id('aasImNotAdult'))->click();
        $client->findElement(WebDriverBy::id('checklist-dismiss-btn'))->click();
        self::waitUntilShows('#artisans');

        // Expect: checklist is dismissed, but only minors-friendly maker shows up

        self::assertInvisible('#checklist-ill-be-careful');
        self::assertInvisible('#TEST001');
        self::assertVisible('#TEST002');
        self::assertInvisible('#btn-reenable-filters');

        // Action: navigate to the data updates page and enable the maker mode, go back to the main page

        $client->request('GET', '/index.php/iu_form/start');

        $client->clickLink('Temporarily disable all the filters and open the main page');
        $client->request('GET', '/index.php/'); // Workaround for the new tab being opened

        // Expect: checklist is hidden and all makers are visible

        self::assertInvisible('#checklist-ill-be-careful');
        self::assertVisible('#TEST001');
        self::assertVisible('#TEST002');
        self::assertVisible('#btn-reenable-filters');

        // Action: click re-enable filters button

        $client->clickLink('Re-enable filters');

        // Expect: main page shows the checklist - filled

        self::assertVisible('#checklist-ill-be-careful');
        self::assertInvisible('#TEST001');
        self::assertInvisible('#TEST002');
        self::assertInvisible('#btn-reenable-filters');

        self::assertSelectorAttributeContains('#checklist-dismiss-btn', 'value', 'I will now click this button');

        // Action: submit the checklist with previous settings kept

        $client->findElement(WebDriverBy::id('checklist-dismiss-btn'))->click();
        self::waitUntilShows('#artisans');

        // Expect: checklist is dismissed, once again only minors-friendly maker shows up

        self::assertInvisible('#checklist-ill-be-careful');
        self::assertInvisible('#TEST001');
        self::assertVisible('#TEST002');
        self::assertInvisible('#btn-reenable-filters');
    }
}
