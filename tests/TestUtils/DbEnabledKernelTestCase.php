<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class DbEnabledKernelTestCase extends KernelTestCase
{
    use DbEnabledTestCaseTrait;
}
