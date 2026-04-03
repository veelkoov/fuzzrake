<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller\Mx;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use Override;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class CreatorUrlsControllerTest extends FuzzrakeWebTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::haveAnAdminUser();
        self::loginAdminUser();
    }

    public function testPageLoads(): void
    {
        self::$client->request('GET', '/mx/creator_urls/');

        self::assertResponseStatusCodeIs(200);
    }
}
