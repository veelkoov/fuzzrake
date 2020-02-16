<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\Controller\DbEnabledWebTestCase;

class EventsControllerTest extends DbEnabledWebTestCase
{
    public function testNewEvent()
    {
        $client = static::createClient();

        $client->request('GET', '/mx/events/new');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEditArtisan()
    {
        $client = static::createClient();
        $event = self::addSimpleGenericEvent();

        $client->request('GET', "/mx/events/{$event->getId()}/edit");

        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
