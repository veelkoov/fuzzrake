<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\EntityManagerTrait;
use App\Utils\TestUtils\TestsBridge;
use Override;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class KernelTestCaseWithEM extends KernelTestCase
{
    use EntityManagerTrait;

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        TestsBridge::reset();
    }
}
