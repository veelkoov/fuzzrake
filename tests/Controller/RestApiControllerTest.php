<?php

declare(strict_types=1);

namespace App\Tests\Controller;

class RestApiControllerTest extends DbEnabledWebTestCase
{
    public function testArtisans()
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/api/artisans.json');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
