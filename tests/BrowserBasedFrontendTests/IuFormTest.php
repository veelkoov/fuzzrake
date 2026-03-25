<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Data\Definitions\Ages;
use App\Tests\TestUtils\Cases\FuzzrakePantherTestCase;
use Exception;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use PHPUnit\Framework\Attributes\Large;

#[Large]
class IuFormTest extends FuzzrakePantherTestCase
{
    /**
     * Assure that:
     * - I/U form state is NOT shared between new creator and existing different creators.
     * - I/U form state is kept until the form gets reset or submitted.
     *
     * @throws Exception
     */
    public function testFormStateIsProperlyKeptAndReset(): void
    {
        // Having two existing creators
        self::persistAndFlush(
            self::getCreator(name: 'Creator 001', creatorId: 'TEST001', ages: Ages::MIXED, nsfwWebsite: false, nsfwSocial: false, doesNsfw: false, worksWithMinors: false),
            self::getCreator(name: 'Creator 002', creatorId: 'TEST002', ages: Ages::MIXED, nsfwWebsite: false, nsfwSocial: false, doesNsfw: false, worksWithMinors: false),
        );

        // Load 1st creator I/U data page, change some stuff A
        $this->goToTheDataPage('TEST001');
        self::assertInputValueSame('iu_form[name]', 'Creator 001');
        self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]' => 'Creator 001 - MODIFIED',
        ]);
        self::$client->getKeyboard()->pressKey(WebDriverKeys::TAB); // Simulate exiting field's focus

        // Load new creator I/U data page, set some stuff B
        $this->goToTheDataPage();
        self::assertInputValueSame('iu_form[name]', '');
        self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]' => 'New creator - MODIFIED',
            'iu_form[creatorId]' => 'TEST003',
            'iu_form[country]' => 'FI',
            'iu_form[ages]' => 'MIXED',
            'iu_form[nsfwWebsite]' => 'NO',
            'iu_form[nsfwSocial]' => 'YES',
            'iu_form[contactAllowed]' => 'NO',
        ]);
        self::$client->getKeyboard()->pressKey(WebDriverKeys::TAB); // Simulate exiting field's focus

        // Load 2nd creator I/U data page, change some stuff C
        $this->goToTheDataPage('TEST002');
        self::assertInputValueSame('iu_form[name]', 'Creator 002');
        self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]' => 'Creator 002 - MODIFIED',
        ]);
        self::$client->getKeyboard()->pressKey(WebDriverKeys::TAB); // Simulate exiting field's focus

        // Go back to 1st creator I/U data page, make sure A matches, submit
        $this->goToTheDataPage('TEST001');
        self::assertInputValueSame('iu_form[name]', 'Creator 001 - MODIFIED');
        $this->selectRightCaptchaSolution();
        self::$client->submit(self::$client->getCrawler()->selectButton('Submit')->form(), [
            'iu_form[password]' => 'test-password',
        ]);
        self::$client->waitFor('#iu-form-data[data-step="confirmation"]');

        // Go back to the new creator I/U data page, make sure B matches, reset
        $this->goToTheDataPage();
        self::assertInputValueSame('iu_form[name]', 'New creator - MODIFIED');
        self::$client->findElement(WebDriverBy::id('iu-form-reset-button'))->click();
        self::$client->getWebDriver()->switchTo()->alert()->accept();
        self::$client->waitFor('#iu-form-data[data-step="data"]');

        // Go back to the 1st creator I/U data page, make sure it's clean
        $this->goToTheDataPage('TEST001');
        self::assertInputValueSame('iu_form[name]', 'Creator 001');

        // Go back to the new creator I/U data page, make sure it's clean
        $this->goToTheDataPage();
        self::assertInputValueSame('iu_form[name]', '');

        // Go back to the 2nd creator I/U data page, make sure C matches
        $this->goToTheDataPage('TEST002');
        self::assertInputValueSame('iu_form[name]', 'Creator 002 - MODIFIED');
    }

    /**
     * @throws WebDriverException
     */
    private function goToTheDataPage(?string $creatorId = null): void
    {
        $isUpdate = null !== $creatorId;

        $iuFormStartUri = '/index.php/iu_form/start'.($isUpdate ? "/$creatorId" : '');
        self::$client->request('GET', $iuFormStartUri);

        self::waitUntilShows('#iu_form_confirmNoPendingUpdates_0');
        self::$client->findElement(WebDriverBy::cssSelector('#iu_form_confirmNoPendingUpdates_0'))->click();

        self::waitUntilShows('#rulesAndContinueButton');
        self::$client->findElement(WebDriverBy::cssSelector('input[type=submit]'))->click();

        self::$client->waitForVisibility('#iu_form_emailAddress', 5); // FIXME
    }
}
