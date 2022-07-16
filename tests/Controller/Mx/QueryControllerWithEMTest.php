<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;

class QueryControllerWithEMTest extends WebTestCaseWithEM
{
    public function testNewArtisan(): void
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
