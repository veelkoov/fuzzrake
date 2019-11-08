<?php

declare(strict_types=1);

namespace App\Tests\Controller;

class MainControllerTest extends DbEnabledWebTestCase
{
    public function testMain(): void
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h4', 'Fursuit makers database');
    }

    public function testRedirectToIuForm(): void
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/redirect_iu_form/TEST');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('GET', '/redirect_iu_form/TEST002');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('GET', '/redirect_iu_form/TEST000');
        static::assertEquals(302, $client->getResponse()->getStatusCode());
    }
}
