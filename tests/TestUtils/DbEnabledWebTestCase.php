<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class DbEnabledWebTestCase extends WebTestCase
{
    use DbEnabledTestCaseTrait;

    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        $result = parent::createClient($options, $server);

        self::resetDB();

        return $result;
    }
}
