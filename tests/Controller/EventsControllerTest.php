<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventsControllerTest extends WebTestCase
{
    public function testEvents()
    {
        $client = static::createClient();

        $client->request('GET', '/events.html');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
