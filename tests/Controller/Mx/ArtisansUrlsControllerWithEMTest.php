<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;

/**
 * @medium
 */
class ArtisansUrlsControllerWithEMTest extends WebTestCaseWithEM
{
    public function testPageLoads(): void
    {
        $client = static::createClient();

        $client->request('GET', '/mx/artisan_urls/');

        static::assertResponseStatusCodeIs($client, 200);
    }
}
