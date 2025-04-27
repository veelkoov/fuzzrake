<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use Override;

/**
 * @medium
 */
class QueryControllerTest extends FuzzrakeWebTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::$client->setServerParameters([
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'testing',
        ]);
    }

    public function testNewCreator(): void
    {
        self::$client->request('GET', '/mx/query/');

        static::assertEquals(200, self::$client->getResponse()->getStatusCode());

        self::$client->submitForm('Run', [
            'query[ITEM_QUERY]' => 'test',
        ]);

        static::assertEquals(200, self::$client->getResponse()->getStatusCode());
    }
}
