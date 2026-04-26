<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class SubmissionsQueueControllerTest extends FuzzrakeWebTestCase
{
    public function testMainLoads(): void
    {
        self::$client->request('GET', 'queue');
        self::assertResponseStatusCodeIs(200);
    }
}
