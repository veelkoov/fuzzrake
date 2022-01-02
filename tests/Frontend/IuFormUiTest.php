<?php

declare(strict_types=1);

namespace App\Tests\Frontend;

use App\Tests\TestUtils\DbEnabledPantherTestCase;
use Exception;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Symfony\Component\Panther\Client;

class IuFormUiTest extends DbEnabledPantherTestCase
{
    private ?Client $client = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createPantherClient();
        $this->client->getCookieJar()->clear();
        self::setWindowSize($this->client, 1600, 900);

        self::persistAndFlush(self::getArtisan(makerId: 'MAKERID', ages: 'ADULTS', worksWithMinors: true));
    }

    /**
     * @throws WebDriverException|Exception
     */
    public function testIForgotPasswordShowsHelp(): void
    {
        $this->getToLastPage();
        usleep(1000000); // Let all the animations end

        self::assertSelectorIsNotVisible('#forgotten_password_instructions');
        $this->client->findElement(WebDriverBy::id('iu_form_changePassword'))->click();
        self::assertSelectorIsVisible('#forgotten_password_instructions');
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
        self::assertSelectorIsVisible('#iu_form_contactInfoObfuscated');
        self::assertSelectorExists('#iu_form_contactInfoObfuscated[required]');

        $form->setValues([
            'iu_form[contactAllowed]' => 'NO',
        ]);

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
        $this->client->request('GET', '/iu_form/start/MAKERID');
        $this->client->waitForVisibility('button.g-recaptcha', 5);

        $this->client->findElement(WebDriverBy::cssSelector('button.g-recaptcha'))->click();
        $this->client->waitForVisibility('#iu_form_name', 5);

        $this->client->getKeyboard()->pressKey(WebDriverKeys::END); // grep-ugly-tests-workarounds Workaround for element not visible bug
        $this->screenshot($this->client); // grep-ugly-tests-workarounds Workaround for element not visible bug

        $this->client->findElement(WebDriverBy::cssSelector('input[type=submit]'))->click();
        $this->client->waitForVisibility('#iu_form_contactInfoObfuscated', 5);
    }
}
