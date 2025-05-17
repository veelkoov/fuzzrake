<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;

/**
 * @medium
 */
class CrawlersControllerTest extends FuzzrakeWebTestCase
{
    public function testSitemap(): void
    {
        self::$client->request('GET', '/sitemap.txt');

        self::assertResponseStatusCodeIs(200);
    }
}
