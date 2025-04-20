<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\EntityManagerTrait;
use App\Tests\TestUtils\Cases\Traits\UtilsTrait;
use App\Utils\TestUtils\TestsBridge;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use LogicException;
use Override;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;

abstract class PantherTestCaseWithEM extends PantherTestCase
{
    use EntityManagerTrait;
    use UtilsTrait;

    protected Client $client;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::stopWebServer(); // This is slow but assures following test won't be broken by "closed entity manager"
        $this->client = static::createPantherClient();
        $this->client->getCookieJar()->clear();
        self::setWindowSize($this->client, 1600, 900);

        self::resetDB();
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        TestsBridge::reset();
    }

    /**
     * @param array<string, string> $options
     * @param array<string, string> $kernelOptions
     * @param array<string, string> $managerOptions
     */
    #[Override]
    protected static function createPantherClient(array $options = [], array $kernelOptions = [], array $managerOptions = []): PantherClient
    {
        $options['hostname'] ??= 'localhost';

        return parent::createPantherClient($options, $kernelOptions, $managerOptions);
    }

    protected static function getPantherClient(): PantherClient
    {
        return self::$pantherClient ?? throw new LogicException('Panther client has not been initialized yet');
    }

    protected static function setWindowSize(PantherClient $client, int $width, int $height): void
    {
        $client->manage()->window()->setSize(new WebDriverDimension($width, $height));
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
        $this->client->findElement(WebDriverBy::xpath('//label[text()="right"]'))->click();
    }
}
