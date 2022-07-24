<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\EntityManagerTrait;
use App\Tests\TestUtils\Cases\Traits\UtilsTrait;
use App\Utils\TestUtils\TestsBridge;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverDimension;
use LogicException;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;

abstract class PantherTestCaseWithEM extends PantherTestCase
{
    use EntityManagerTrait;
    use UtilsTrait;

    protected function tearDown(): void
    {
        parent::tearDown();

        TestsBridge::reset();
        self::stopWebServer();
    }

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
    protected static function waitUntilShows(string $locator, int $millisecondsForAnimation = 200): void
    {
        usleep($millisecondsForAnimation * 1000);
        self::getPantherClient()->waitForVisibility($locator, 1);
    }

    protected static function assertVisible(string $locator): void
    {
        $exception = null;

        try {
            self::getPantherClient()->waitForVisibility($locator, 1);
        } catch (NoSuchElementException|TimeoutException $caught) {
            $exception = $caught;
        }

        self::assertNull($exception, "Element '$locator' is not visible.");
    }

    protected static function assertInvisible(string $locator): void
    {
        $exception = null;

        try {
            self::getPantherClient()->waitForInvisibility($locator, 1);
        } catch (NoSuchElementException|TimeoutException $caught) {
            $exception = $caught;
        }

        self::assertNull($exception, "Element '$locator' is visible.");
    }
}
