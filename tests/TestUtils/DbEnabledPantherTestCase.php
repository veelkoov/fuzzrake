<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;

abstract class DbEnabledPantherTestCase extends PantherTestCase
{
    use DbEnabledTestCaseTrait;

    protected static function createPantherClient(array $options = [], array $kernelOptions = [], array $managerOptions = []): PantherClient
    {
        $options['hostname'] ??= 'fuzzrake';

        $result = parent::createPantherClient($options, $kernelOptions, $managerOptions);

        self::resetDB();

        return $result;
    }

    protected static function setWindowSize(PantherClient $client, int $width, int $height): void
    {
        $client->manage()->window()->setSize(new WebDriverDimension($width, $height));
    }

    protected function screenshot(WebDriver $client): void
    {
        $somekindoftrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, limit: 1);

        $class = str_replace('\\', '/', $somekindoftrace[0]['class']);
        $class = pattern('^App/Tests/')->prune($class);

        $line = $somekindoftrace[0]['line'];

        $client->takeScreenshot("var/screenshots/$class/$line.png");
    }
}
