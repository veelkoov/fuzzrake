<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\DbEnabledWebTestCase;

class EventsControllerTest extends DbEnabledWebTestCase
{
    public function testEvents()
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/events.html');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('p', 'All times are UTC');
    }
}
