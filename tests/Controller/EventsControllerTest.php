<?php

declare(strict_types=1);

namespace App\Tests\Controller;

class EventsControllerTest extends DbEnabledWebTestCase
{
    public function testEvents()
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/events.html');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('p', 'All times are UTC');
    }
}
