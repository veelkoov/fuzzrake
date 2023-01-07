<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use App\Utils\Json;
use JsonException;

/**
 * @medium
 */
class RestApiControllerTest extends WebTestCaseWithEM
{
    /**
     * @throws JsonException
     */
    public function testArtisansArrayOk(): void
    {
        $client = static::createClient();

        self::persistAndFlush(
            self::getArtisan(makerId: 'MAKER01', country: 'FI'),
            self::getArtisan(makerId: 'MAKER02', country: 'CZ'),
        );

        $client->request('GET', '/api/artisans-array.json?countries[]=FI&wantsSfw=0&isAdult=1');
        self::assertResponseStatusCodeSame(200);

        self::assertEquals('application/json', $client->getResponse()->headers->get('content-type'));
        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);

        $data = Json::decode($content);
        self::assertIsArray($data);
        self::assertCount(1, $data);
        self::assertEquals('MAKER01', $data[0][0] ?? null);
    }

    public function testArtisansArrayCoercionFailed(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/artisans-array.json?countries=FI');
        self::assertResponseStatusCodeSame(400);
    }
}
