<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\MocksTrait;
use PHPUnit\Framework\TestCase;

abstract class FuzzrakeTestCase extends TestCase
{
    use MocksTrait;
}
