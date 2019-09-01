<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RestApiControllerTest extends WebTestCase
{
    public function testArtisans()
    {
        $client = static::createClient();

        $client->request('GET', '/api/artisans.json');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
