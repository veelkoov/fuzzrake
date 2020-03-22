<?php

declare(strict_types=1);

namespace App\Tests\Controller;

class StatisticsControllerTest extends DbEnabledWebTestCase
{
    public function testStatistics()
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/statistics.html');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h1#data_statistics', 'Data statistics');
    }
}
