<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\BrowserTrait;
use App\Tests\TestUtils\Cases\Traits\CacheTrait;
use App\Tests\TestUtils\Cases\Traits\EntityManagerTrait;
use App\Utils\TestUtils\TestsBridge;
use Facebook\WebDriver\WebDriverDimension;
use Override;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

abstract class FuzzrakePantherTestCase extends PantherTestCase
{
    use BrowserTrait;
    use CacheTrait;
    use EntityManagerTrait;

    protected static Client $client;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::stopWebServer(); // This is slow but assures following test won't be broken by "closed entity manager"
        self::$client = static::createPantherClient();
        self::$client->getCookieJar()->clear();
        self::$client->manage()->window()->setSize(new WebDriverDimension(1600, 900));

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
    protected static function createPantherClient(array $options = [], array $kernelOptions = [], array $managerOptions = []): Client
    {
        $options['hostname'] ??= 'localhost';

        return parent::createPantherClient($options, $kernelOptions, $managerOptions);
    }
}
