<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\DbEnabledWebTestCase;

class MainControllerTest extends DbEnabledWebTestCase
{
    public function testMainPageLoads(): void
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h4', 'Fursuit makers database');
    }
}
