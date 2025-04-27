<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\MocksTrait;
use App\Utils\TestUtils\TestsBridge;
use Override;
use PHPUnit\Framework\TestCase;

abstract class FuzzrakeTestCase extends TestCase
{
    use MocksTrait;

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        TestsBridge::reset();
    }
}
