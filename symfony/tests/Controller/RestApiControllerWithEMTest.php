<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use App\Utils\Arrays\ArrayReader;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * @medium
 */
class RestApiControllerWithEMTest extends WebTestCaseWithEM
{
    /**
     * @throws JsonException
     */
    public function testCreators(): void
    {
        $client = static::createClient();
        self::persistAndFlush(self::getArtisan('API testing artisan', 'APIARTS', 'FI'));

        $client->request('GET', '/api/artisans.json');
        self::assertResponseStatusCodeIs($client, 200);

        $text = $client->getResponse()->getContent();
        self::assertNotFalse($text);

        $parsedJson = Json::decode($text, Json::FORCE_ARRAY);
        $arrayReader = ArrayReader::of($parsedJson);

        self::assertEquals('API testing artisan', $arrayReader->getNonEmptyString('[0][NAME]'));
        self::assertEquals('APIARTS', $arrayReader->getNonEmptyString('[0][MAKER_ID]'));
        self::assertEquals('FI', $arrayReader->getNonEmptyString('[0][COUNTRY]'));
    }
}
