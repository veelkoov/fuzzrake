<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class CrawlersControllerTest extends FuzzrakeWebTestCase
{
    public function testSitemap(): void
    {
        self::$client->request('GET', '/sitemap.txt');

        self::assertResponseStatusCodeIs(200);
    }
}
