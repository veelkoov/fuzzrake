<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use App\Tests\TestUtils\Cases\Traits\FiltersTestTrait;
use App\Utils\Json;
use JsonException;

/**
 * @medium
 */
class FiltersTest extends PantherTestCaseWithEM
{
    use FiltersTestTrait;

    /**
     * @dataProvider filterChoicesDataProvider
     *
     * @param array<string, list<string>|bool> $filtersSet
     * @param list<string>                     $expectedMakerIds
     *
     * @throws JsonException
     */
    public function testFiltersThroughApi(array $filtersSet, array $expectedMakerIds): void
    {
        $client = static::createClient();

        self::persistAndFlush(...$this->getTestArtisans());

        $queryParts = [];

        foreach ($filtersSet as $name => $value) {
            if (is_bool($value)) {
                $queryParts[] = $name.'='.(int) $value;
            } else {
                foreach ($value as $item) {
                    $queryParts[] = $name.'[]='.urlencode($item);
                }
            }
        }

        if (!array_key_exists('wantsSfw', $filtersSet)) {
            $queryParts[] = 'wantsSfw=0';
        }

        if (!array_key_exists('isAdult', $filtersSet)) {
            $queryParts[] = 'isAdult=1';
        }

        $query = implode('&', $queryParts);

        $client->request('GET', '/api/artisans-array.json?'.$query);
        self::assertResponseStatusCodeSame(200);

        self::assertEquals('application/json', $client->getResponse()->headers->get('content-type'));
        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);

        $data = Json::decode($content);
        self::assertIsArray($data);

        $resultMakerIds = [];

        foreach ($data as $artisanData) {
            $resultMakerIds[] = $artisanData[0];
        }

        self::assertEquals($expectedMakerIds, $resultMakerIds, "$query query failed.");
    }
}
