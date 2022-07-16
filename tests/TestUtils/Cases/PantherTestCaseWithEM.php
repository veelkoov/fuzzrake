<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\EntityManagerTrait;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverDimension;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;

abstract class PantherTestCaseWithEM extends PantherTestCase
{
    use EntityManagerTrait;

    /**
     * @param array<string, string> $options
     * @param array<string, string> $kernelOptions
     * @param array<string, string> $managerOptions
     */
    protected static function createPantherClient(array $options = [], array $kernelOptions = [], array $managerOptions = []): PantherClient
    {
        $options['hostname'] ??= 'localhost';

        $result = parent::createPantherClient($options, $kernelOptions, $managerOptions);

        self::resetDB();

        return $result;
    }

    protected function tearDown(): void
    {
        self::stopWebServer();
    }

    protected static function setWindowSize(PantherClient $client, int $width, int $height): void
    {
        $client->manage()->window()->setSize(new WebDriverDimension($width, $height));
    }

    /**
     * @throws NoSuchElementException|TimeoutException
     */
    protected static function waitUntilShows(string $locator, int $millisecondsForAnimation = 200): void
    {
        usleep($millisecondsForAnimation * 1000);
        self::$pantherClient->waitForVisibility($locator, 1);
    }

    protected static function assertVisible(string $locator): void
    {
        $exception = null;

        try {
            self::$pantherClient->waitForVisibility($locator, 1);
        } catch (NoSuchElementException|TimeoutException $caught) {
            $exception = $caught;
        }

        self::assertNull($exception, "Element '$locator' is not visible.");
    }

    protected static function assertInvisible(string $locator): void
    {
        $exception = null;

        try {
            self::$pantherClient->waitForInvisibility($locator, 1);
        } catch (NoSuchElementException|TimeoutException $caught) {
            $exception = $caught;
        }

        self::assertNull($exception, "Element '$locator' is visible.");
    }
}
