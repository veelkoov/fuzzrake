<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use App\Utils\Json;
use JsonException;

class RestApiControllerTestWithEM extends WebTestCaseWithEM
{
    public function testArtisans(): void
    {
        $client = static::createClient();
        self::persistAndFlush(self::getArtisan('API testing artisan', 'APIARTS', 'FI'));

        $client->request('GET', '/api/artisans.json');
        self::assertResponseStatusCodeSame(200);

        $text = $client->getResponse()->getContent();
        self::assertStringContainsString('"API testing artisan"', $text);
        self::assertStringContainsString('"APIARTS"', $text);
        self::assertStringContainsString('"FI"', $text);
    }

    /**
     * @throws JsonException
     */
    public function testHealth(): void
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/health');
        self::assertResponseStatusCodeSame(200);

        $data = Json::decode($client->getResponse()->getContent());
        self::assertEquals('OK', $data['status']);
    }
}