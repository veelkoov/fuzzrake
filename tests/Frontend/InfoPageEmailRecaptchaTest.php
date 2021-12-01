<?php

declare(strict_types=1);

namespace App\Tests\Frontend;

use App\Tests\TestUtils\DbEnabledPantherTestCase;
use Facebook\WebDriver\Exception\WebDriverException;

class InfoPageEmailRecaptchaTest extends DbEnabledPantherTestCase
{
    /**
     * @throws WebDriverException
     */
    public function testRecaptchaWorksAndEmailAddressAppears(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/index.php/info.html');

        $client->waitForVisibility('a[href^="mailto:"]', 5);
        self::assertTrue(true); // If the above did not timed out, we're good
    }
}
