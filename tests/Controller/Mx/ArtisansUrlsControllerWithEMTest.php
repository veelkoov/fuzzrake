<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;

class ArtisansUrlsControllerWithEMTest extends WebTestCaseWithEM
{
    public function testPageLoads()
    {
        $client = static::createClient();

        $client->request('GET', '/mx/artisan_urls/');

        static::assertResponseStatusCodeSame(200);
    }
}
