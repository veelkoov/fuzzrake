<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use DateTime;
use DateTimeZone;

class RestApiControllerTest extends DbEnabledWebTestCase
{
    public function testArtisans()
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/api/artisans.json');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @group time-sensitive
     */
    public function testHealth()
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/health');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent());
        $this->assertEquals('OK', $data->status);
        $this->assertEquals(DateTime::createFromFormat('U', (string) time(), new DateTimeZone('UTC'))->format('Y-m-d H:i'), $data->lastCstRunUtc);
    }
}
