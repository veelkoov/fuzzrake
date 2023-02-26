<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\EntityManagerTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

abstract class WebTestCaseWithEM extends WebTestCase // TODO: Why is this slower than Panther tests?
{
    use EntityManagerTrait;

    /**
     * @param array<string, string> $options
     * @param array<string, string> $server
     */
    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        $result = parent::createClient($options, $server);

        self::resetDB();

        return $result;
    }
}
