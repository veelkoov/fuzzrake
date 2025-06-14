<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\AssertsTrait;
use App\Tests\TestUtils\Cases\Traits\CacheTrait;
use App\Tests\TestUtils\Cases\Traits\EntityManagerTrait;
use Override;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class FuzzrakeKernelTestCase extends KernelTestCase
{
    use AssertsTrait;
    use CacheTrait;
    use EntityManagerTrait;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        self::resetDB();
    }
}
