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
        $this->screenshot($this->client);

        self::assertSelectorIsVisible('#iu_form_contactInfoObfuscated');
        self::assertSelectorExists('#iu_form_contactInfoObfuscated[required]');

        $form->setValues([
            'iu_form[contactAllowed]' => 'NO',
        ]);
        $this->screenshot($this->client);

        self::assertSelectorIsNotVisible('#iu_form_contactInfoObfuscated');
        self::assertSelectorExists('#iu_form_contactInfoObfuscated:not([required])');
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
