<?php

declare(strict_types=1);

namespace App\Tests\Controller;

class MainControllerTest extends DbEnabledWebTestCase
{
    public function testMain()
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h4', 'Fursuit makers database');
    }
}
