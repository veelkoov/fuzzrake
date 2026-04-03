<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller\Mx;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use Override;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class QueryControllerTest extends FuzzrakeWebTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::haveAnAdminUser();
        self::loginAdminUser();
    }

    public function testNewCreator(): void
    {
        self::$client->request('GET', '/mx/query/');

        self::assertResponseStatusCodeIs(200);

        self::$client->submitForm('Run', [
            'query[ITEM_QUERY]' => 'test',
        ]);

        self::assertResponseStatusCodeIs(200);
    }
}
