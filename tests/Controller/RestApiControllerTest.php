<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\DbEnabledWebTestCase;
use App\Utils\Json;
use JsonException;

class RestApiControllerTest extends DbEnabledWebTestCase
{
    public function testArtisans()
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/api/artisans.json');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @throws JsonException
     */
    public function testHealth()
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/health');
        static::assertEquals(200, $client->getResponse()->getStatusCode());

        $data = Json::decode($client->getResponse()->getContent());
        static::assertEquals('OK', $data['status']);
    }
}
