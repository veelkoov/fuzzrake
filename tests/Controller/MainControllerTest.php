<?php

declare(strict_types=1);

namespace App\Tests\Controller;

class MainControllerTest extends DbEnabledWebTestCase
{
    public function testMain(): void
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h4', 'Fursuit makers database');
    }
}
