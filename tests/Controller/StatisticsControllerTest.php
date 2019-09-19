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

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1#data_statistics', 'Data statistics');
    }

    public function testOrdering()
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/ordering.html');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Other items, combined');
    }
}
