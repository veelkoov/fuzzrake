<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Utils\Collections\ArrayReader;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class RestApiControllerTest extends FuzzrakeWebTestCase
{
    /**
     * @throws JsonException
     */
    public function testCreators(): void
    {
        self::persistAndFlush(self::getCreator('API testing creator', 'TEST001', 'FI'));

        self::$client->request('GET', '/api/artisans.json');
        self::assertResponseStatusCodeIs(200);

        $text = self::$client->getResponse()->getContent();
        self::assertNotFalse($text);

        $parsedJson = Json::decode($text, Json::FORCE_ARRAY);
        $arrayReader = ArrayReader::of($parsedJson);

        self::assertSame('API testing creator', $arrayReader->getNonEmptyString('[0][NAME]'));
        self::assertSame('TEST001', $arrayReader->getNonEmptyString('[0][MAKER_ID]'));
        self::assertSame('FI', $arrayReader->getNonEmptyString('[0][COUNTRY]'));
    }
}
