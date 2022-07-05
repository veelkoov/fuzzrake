<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\EntityManagerTrait;
use Facebook\WebDriver\WebDriver;
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

    protected static function setWindowSize(PantherClient $client, int $width, int $height): void
    {
        $client->manage()->window()->setSize(new WebDriverDimension($width, $height));
    }
}
