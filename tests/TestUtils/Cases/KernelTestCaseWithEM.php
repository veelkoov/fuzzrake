<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\EntityManagerTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class KernelTestCaseWithEM extends KernelTestCase
{
    use EntityManagerTrait;
}
