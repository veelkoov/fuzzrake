<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\Controller\DbEnabledWebTestCase;

class QueryControllerTest extends DbEnabledWebTestCase
{
    public function testNewArtisan()
    {
        $client = static::createClient();

        $client->request('GET', '/mx/query/');

        static::assertEquals(200, $client->getResponse()->getStatusCode());

        $client->submitForm('Run', [
            'query[ITEM_QUERY]' => 'test',
        ]);

        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
