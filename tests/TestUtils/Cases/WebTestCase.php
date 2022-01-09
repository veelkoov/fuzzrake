<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\UtilsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;

abstract class WebTestCase extends SymfonyWebTestCase
{
    use UtilsTrait;
}
