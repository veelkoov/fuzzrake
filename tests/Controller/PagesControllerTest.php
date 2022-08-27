<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PagesControllerTest extends WebTestCase
{
    public function testInfo(): void
    {
        $client = static::createClient();

        $client->request('GET', '/info');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h3#contact', 'Contact maintainer');
        static::assertSelectorTextContains('h3#data-updates', 'How to add/update your studio/maker info');
    }

    public function testTracking(): void
    {
        $client = static::createClient();

        $client->request('GET', '/tracking');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h1', 'Automatic tracking and status updates');
    }

    public function testMakerIds(): void
    {
        $client = static::createClient();

        $client->request('GET', '/maker-ids');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h1', 'What is a "maker ID"?');
    }

    public function testDonate(): void
    {
        $client = static::createClient();

        $client->request('GET', '/donate');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h2', 'Please donate');
    }

    public function testRules(): void
    {
        $client = static::createClient();

        $client->request('GET', '/rules');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h1', 'Rules for makers/studios');
    }

    public function testShouldKnow(): void
    {
        $client = static::createClient();

        $client->request('GET', '/should-know');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h1', 'What you should know');
    }
}
