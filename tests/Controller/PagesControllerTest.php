<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PagesControllerTest extends WebTestCase
{
    public function testInfo()
    {
        $client = static::createClient();

        $client->request('GET', '/info.html');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h3#contact', 'Contact maintainer');
        static::assertSelectorTextContains('h3#data-updates', 'How to add/update your studio/maker info');
    }

    public function testTracking()
    {
        $client = static::createClient();

        $client->request('GET', '/tracking.html');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h1', 'Automatic tracking and status updates');
    }

    public function testMakerIds()
    {
        $client = static::createClient();

        $client->request('GET', '/maker_ids.html');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h1', 'What is a "maker ID"?');
    }

    public function testDonate()
    {
        $client = static::createClient();

        $client->request('GET', '/donate.html');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h2', 'Please donate');
    }

    public function testRules()
    {
        $client = static::createClient();

        $client->request('GET', '/rules.html');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h1', 'Rules for makers/studios');
    }
}
