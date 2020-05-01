<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Utils\DateTime\DateTimeUtils;
use DateTime;
use DateTimeZone;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @group time-sensitive
 */
class RestApiControllerTest extends DbEnabledWebTestCase
{
    public function testArtisans()
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/api/artisans.json');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testHealth()
    {
        ClockMock::register(DateTimeUtils::class);

        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/health');

        static::assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent());
        static::assertEquals('OK', $data->status);
        static::assertEquals(DateTime::createFromFormat('U', (string) time(), new DateTimeZone('UTC'))->format('Y-m-d H:i'), $data->lastCstRunUtc);
        static::assertEquals(DateTime::createFromFormat('U', (string) time(), new DateTimeZone('UTC'))->format('Y-m-d H:i:s'), $data->serverTimeUtc);
    }
}
