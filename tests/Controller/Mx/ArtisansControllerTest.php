<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\Controller\DbEnabledWebTestCase;

class ArtisansControllerTest extends DbEnabledWebTestCase
{
    public function testNewArtisan()
    {
        $client = static::createClient();

        $client->request('GET', '/mx/artisans/new');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEditArtisan()
    {
        $client = static::createClient();
        $artisan = self::addSimpleArtisan();

        $client->request('GET', "/mx/artisans/{$artisan->getId()}/edit");

        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
