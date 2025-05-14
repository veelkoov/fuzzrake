<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\CacheTrait;
use App\Tests\TestUtils\Cases\Traits\CaptchaTrait;
use App\Tests\TestUtils\Cases\Traits\EntityManagerTrait;
use App\Tests\TestUtils\Cases\Traits\FormsTrait;
use App\Utils\TestUtils\TestsBridge;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FuzzrakeWebTestCase extends WebTestCase
{
    use CacheTrait;
    use EntityManagerTrait;
    use FormsTrait;
    use CaptchaTrait;

    protected static KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        self::$client = static::createClient();
        self::resetDB();
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        TestsBridge::reset();
    }
}
