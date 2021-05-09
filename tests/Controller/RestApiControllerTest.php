<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\DbEnabledWebTestCase;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Json;
use DateTime;
use DateTimeZone;
use JsonException;
use Symfony\Bridge\PhpUnit\ClockMock;

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
        ClockMock::register(DateTimeUtils::class);
        ClockMock::register(RestApiControllerTest::class);

        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/health');

        static::assertEquals(200, $client->getResponse()->getStatusCode());

        $data = Json::decode($client->getResponse()->getContent());
        static::assertEquals('OK', $data['status']);
        static::assertEquals(DateTime::createFromFormat('U', (string) time(), new DateTimeZone('UTC'))->format('Y-m-d H:i'), $data['lastCstRunUtc']);
        static::assertEquals(DateTime::createFromFormat('U', (string) time(), new DateTimeZone('UTC'))->format('Y-m-d H:i:s'), $data['serverTimeUtc']);
    }
}
