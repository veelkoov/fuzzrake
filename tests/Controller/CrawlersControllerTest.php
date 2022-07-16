<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CrawlersControllerTest extends WebTestCase
{
    public function testSitemap(): void
    {
        $client = static::createClient();

        $client->request('GET', '/sitemap.txt');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
