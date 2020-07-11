<?php

declare(strict_types=1);

namespace App\Tests\Controller;

class IuFormControllerTest extends DbEnabledWebTestCase
{
    public function testIuForm(): void
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/iu_form/TEST');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('GET', '/iu_form/TEST002');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('GET', '/iu_form/TEST000');
        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
