<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Panther\PantherTestCase;

abstract class DbEnabledPantherTestCase extends PantherTestCase
{
    use DbEnabledTestCaseTrait;

    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        $result = parent::createClient($options, $server);

        /* @noinspection PhpFieldAssignmentTypeMismatchInspection */
        self::$entityManager = self::$container->get('doctrine.orm.default_entity_manager');

        SchemaTool::resetOn(self::$entityManager);

        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return $result;
    }
}
