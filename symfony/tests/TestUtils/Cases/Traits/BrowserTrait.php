<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
use LogicException;
use Symfony\Component\Panther\Client;

trait BrowserTrait
{
    private static function getPantherClient(): Client
    {
        return self::$pantherClient ?? throw new LogicException('Panther client has not been initialized yet');
    }

    /**
     * @throws NoSuchElementException|TimeoutException
     */
    protected static function waitUntilShows(string $locator, int $millisecondsForAnimation = 500): void
    {
        usleep($millisecondsForAnimation * 1000);
        self::assertSelectorExists($locator);

        try {
            self::getPantherClient()->waitForVisibility($locator, 5);
        } catch (TimeoutException) {
            self::fail("$locator did not become visible");
        }
    }

    /**
     * @throws NoSuchElementException
     */
    protected static function waitUntilHides(string $locator, int $millisecondsForAnimation = 500): void
    {
        usleep($millisecondsForAnimation * 1000);
        self::assertSelectorExists($locator);

        try {
            self::getPantherClient()->waitForInvisibility($locator, 5);
        } catch (TimeoutException) {
            self::fail("$locator did not become invisible");
        }
    }

    protected static function assertVisible(string $locator, string $message = null): void
    {
        $exception = null;

        try {
            self::getPantherClient()->waitForVisibility($locator, 5);
        } catch (NoSuchElementException|TimeoutException $caught) {
            $exception = $caught;
        }

        self::assertNull($exception, $message ?? "Element '$locator' is not visible.");
    }

    protected static function assertInvisible(string $locator, string $message = null): void
    {
        $exception = null;

        try {
            self::getPantherClient()->waitForInvisibility($locator, 5);
        } catch (NoSuchElementException|TimeoutException $caught) {
            $exception = $caught;
        }

        self::assertNull($exception, $message ?? "Element '$locator' is visible.");
    }

    /**
     * @throws NoSuchElementException
     */
    protected function selectRightCaptchaSolution(): void
    {
        self::$client->findElement(WebDriverBy::xpath('//label[text()="right"]'))->click();
    }
}
