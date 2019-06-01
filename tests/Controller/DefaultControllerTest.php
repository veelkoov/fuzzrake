<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testMain()
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testInfo()
    {
        $client = static::createClient();

        $client->request('GET', '/info.html');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testTracking()
    {
        $client = static::createClient();

        $client->request('GET', '/tracking.html');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testWhoopsies()
    {
        $client = static::createClient();

        $client->request('GET', '/whoopsies.html');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testMakerIds()
    {
        $client = static::createClient();

        $client->request('GET', '/maker_ids.html');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
