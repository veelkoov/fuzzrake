<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Data\Definitions\Ages;
use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use Exception;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\Client;

/**
 * @large
 */
class IuFormTest extends PantherTestCaseWithEM
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = static::createPantherClient();
        $this->client->getCookieJar()->clear();
        self::setWindowSize($this->client, 1600, 900);

        self::persistAndFlush(self::getArtisan(
            makerId: 'MAKERID',
            ages: Ages::MINORS, // ages === MINORS & nsfwSocial === true tests dynamic "doesNsfw" and "worksWithMinors"
            nsfwWebsite: false,
            nsfwSocial: true,
        ));
    }

    /**
     * @throws Exception
     */
    public function testIForgotPasswordShowsHelp(): void
    {
        $this->getToLastPage();

        self::waitUntilHides('#forgotten_password_instructions');
        $this->client->findElement(WebDriverBy::id('iu_form_changePassword'))->click();
        self::waitUntilShows('#forgotten_password_instructions');
    }

    /**
     * @throws Exception
     */
    public function testContactMethodNotRequiredAndHiddenWhenContactNotAllowed(): void
    {
        $this->getToLastPage();

        $form = $this->client->getCrawler()->selectButton('Submit')->form([
            'iu_form[contactAllowed]' => 'FEEDBACK',
        ]);
        $this->client->waitForVisibility('#iu_form_contactInfoObfuscated', 5);

        $this->client->waitForVisibility('#iu_form_contactInfoObfuscated', 5);
        self::assertSelectorIsVisible('#iu_form_contactInfoObfuscated');
        self::assertSelectorExists('#iu_form_contactInfoObfuscated[required]');

        $form->setValues([
            'iu_form[contactAllowed]' => 'NO',
        ]);
        $this->client->waitForInvisibility('#iu_form_contactInfoObfuscated', 5);

        $this->client->waitForInvisibility('#iu_form_contactInfoObfuscated', 5);
        self::assertSelectorIsNotVisible('#iu_form_contactInfoObfuscated');
        self::assertSelectorExists('#iu_form_contactInfoObfuscated:not([required])');
    }

    /**
     * @throws Exception
     */
    public function testContactAllowanceProsConsAreToggling(): void
    {
        $this->getToLastPage();
        $form = $this->client->getCrawler()->selectButton('Submit')->form();

        $noSelectionYet = '.pros-cons-contact-options[data-min-level="-1"][data-max-level="-1"]';
        $neverOnly = '.pros-cons-contact-options[data-min-level="0"][data-max-level="0"]';
        $feedbackOnly = '.pros-cons-contact-options[data-min-level="3"][data-max-level="3"]';
        $anythingButFeedback = '.pros-cons-contact-options[data-min-level="0"][data-max-level="2"]';

        $this->client->waitForVisibility($noSelectionYet, 5);
        $this->client->waitForInvisibility($neverOnly, 5);
        $this->client->waitForInvisibility($anythingButFeedback, 5);
        $this->client->waitForInvisibility($feedbackOnly, 5);

        $form->setValues([
            'iu_form[contactAllowed]' => 'NO',
        ]);

        $this->client->waitForInvisibility($noSelectionYet, 5);
        $this->client->waitForVisibility($neverOnly, 5);
        $this->client->waitForVisibility($anythingButFeedback, 5);
        self::assertSelectorIsNotVisible($feedbackOnly);

        $form->setValues([
            'iu_form[contactAllowed]' => 'FEEDBACK',
        ]);

        self::assertSelectorIsNotVisible($noSelectionYet);
        $this->client->waitForInvisibility($neverOnly, 5);
        $this->client->waitForInvisibility($anythingButFeedback, 5);
        $this->client->waitForVisibility($feedbackOnly, 5);
    }

    /**
     * @throws Exception
     */
    private function getToLastPage(): void
    {
        $this->client->request('GET', '/index.php/iu_form/start/MAKERID');

        self::waitUntilShows('#iu_form_confirmUpdatingTheRightOne_0');
        $this->client->findElement(WebDriverBy::cssSelector('#iu_form_confirmUpdatingTheRightOne_0'))->click();

        self::waitUntilShows('#iu_form_confirmYouAreTheMaker_0');
        $this->client->findElement(WebDriverBy::cssSelector('#iu_form_confirmYouAreTheMaker_0'))->click();

        self::waitUntilShows('#iu_form_confirmNoPendingUpdates_0');
        $this->client->findElement(WebDriverBy::cssSelector('#iu_form_confirmNoPendingUpdates_0'))->click();

        self::waitUntilShows('#rulesAndContinueButton');
        $this->client->findElement(WebDriverBy::cssSelector('input[type=submit]'))->click();

        $this->client->waitForVisibility('#iu_form_name', 5);
        $this->client->findElement(WebDriverBy::cssSelector('input[type=submit]'))->click();

        $this->client->waitForVisibility('#iu_form_contactInfoObfuscated', 5);
    }
}
