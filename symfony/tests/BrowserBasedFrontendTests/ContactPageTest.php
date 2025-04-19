<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;

/**
 * @large
 */
class ContactPageTest extends PantherTestCaseWithEM
{
    /**
     * @throws WebDriverException
     */
    public function testCaptchaWorksAndEmailAddressAppears(): void
    {
        $this->client->request('GET', '/index.php/contact');

        // E-mail address link is not visible by default
        self::assertSelectorNotExists('#protected-contact-info a');

        // Solve the captcha
        $this->selectRightCaptchaSolution();
        $this->client->findElement(WebDriverBy::xpath('//input[@type="submit" and @value="Reveal email address"]'))->click();

        // Wait until the page loads
        $this->client->waitForVisibility('#protected-contact-info a', 5);

        // The link should now contain the e-mail address
        self::assertSelectorAttributeContains('#protected-contact-info a', 'href', 'mailto:');
    }
}
