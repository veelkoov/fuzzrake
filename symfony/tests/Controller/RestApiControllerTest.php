<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Utils\Collections\ArrayReader;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * @medium
 */
class RestApiControllerTest extends FuzzrakeWebTestCase
{
    /**
     * @throws JsonException
     */
    public function testCreators(): void
    {
        $client = static::createClient();
        self::persistAndFlush(self::getCreator('API testing creator', 'TEST001', 'FI'));

        $client->request('GET', '/api/artisans.json');
        self::assertResponseStatusCodeIs($client, 200);

        $text = $client->getResponse()->getContent();
        self::assertNotFalse($text);

        $parsedJson = Json::decode($text, Json::FORCE_ARRAY);
        $arrayReader = ArrayReader::of($parsedJson);

        self::assertEquals('API testing creator', $arrayReader->getNonEmptyString('[0][NAME]'));
        self::assertEquals('TEST001', $arrayReader->getNonEmptyString('[0][MAKER_ID]'));
        self::assertEquals('FI', $arrayReader->getNonEmptyString('[0][COUNTRY]'));
    }
}
