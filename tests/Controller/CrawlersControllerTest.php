<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CrawlersControllerTest extends WebTestCase
{
    public function testRobots()
    {
        $client = static::createClient();

        $client->request('GET', '/robots.txt');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testSitemap()
    {
        $client = static::createClient();

        $client->request('GET', '/sitemap.txt');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
