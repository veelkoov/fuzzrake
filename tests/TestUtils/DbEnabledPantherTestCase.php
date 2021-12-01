<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use Facebook\WebDriver\WebDriverDimension;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;

abstract class DbEnabledPantherTestCase extends PantherTestCase
{
    use DbEnabledTestCaseTrait;

    protected static function createPantherClient(array $options = [], array $kernelOptions = [], array $managerOptions = []): PantherClient
    {
        $options['hostname'] ??= 'fuzzrake';
        $options['webServerDir'] ??= __DIR__.'/../../public/'; // Workaround PantherTestCase::$webServerDir access before initialization

        $result = parent::createPantherClient($options, $kernelOptions, $managerOptions);

        self::resetDB();

        return $result;
    }

    protected static function setWindowSize(PantherClient $client, int $width, int $height): void
    {
        $client->manage()->window()->setSize(new WebDriverDimension($width, $height));
    }
}
