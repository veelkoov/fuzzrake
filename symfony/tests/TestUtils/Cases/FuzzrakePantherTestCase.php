<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\AssertsTrait;
use App\Tests\TestUtils\Cases\Traits\CacheTrait;
use App\Tests\TestUtils\Cases\Traits\EntityManagerTrait;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Override;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

abstract class FuzzrakePantherTestCase extends PantherTestCase
{
    use AssertsTrait;
    use CacheTrait;
    use EntityManagerTrait;

    protected static Client $client;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::stopWebServer(); // This is slow but assures following test won't be broken by "closed entity manager"
        self::$client = static::createPantherClient(['hostname' => 'localhost']);
        self::$client->getCookieJar()->clear();
        self::$client->manage()->window()->setSize(new WebDriverDimension(1600, 900));

        self::resetDB();
    }

    /**
     * @throws NoSuchElementException|TimeoutException
     */
    protected static function waitUntilShows(string $locator, int $millisecondsForAnimation = 500): void
    {
        usleep($millisecondsForAnimation * 1000);
        self::assertSelectorExists($locator);

        try {
            self::$client->waitForVisibility($locator, 5);
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
            self::$client->waitForInvisibility($locator, 5);
        } catch (TimeoutException) {
            self::fail("$locator did not become invisible");
        }
    }

    protected static function assertVisible(string $locator, string $message = null): void
    {
        $exception = null;

        try {
            self::$client->waitForVisibility($locator, 5);
        } catch (NoSuchElementException|TimeoutException $caught) {
            $exception = $caught;
        }

        self::assertNull($exception, $message ?? "Element '$locator' is not visible.");
    }

    protected static function assertInvisible(string $locator, string $message = null): void
    {
        $exception = null;

        try {
            self::$client->waitForInvisibility($locator, 5);
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
