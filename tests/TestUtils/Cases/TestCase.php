<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Utils\TestUtils\TestsBridge;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        TestsBridge::reset();
    }
}
