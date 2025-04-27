<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\AssertsTrait;
use App\Tests\TestUtils\Cases\Traits\FiltersTestTrait;
use App\Tests\TestUtils\FiltersData;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use JsonException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @medium
 */
class MainControllerFiltersTest extends FuzzrakeWebTestCase
{
    use AssertsTrait;
    use FiltersTestTrait;

    /**
     * @dataProvider filterChoicesDataProvider
     *
     * @param list<Creator>                    $creators
     * @param array<string, list<string>|bool> $filtersSet
     * @param list<string>                     $expectedCreatorIds
     *
     * @throws JsonException
     */
    public function testFiltersThroughHtmx(array $creators, array $filtersSet, array $expectedCreatorIds): void
    {
        self::persistAndFlush(...$creators, ...FiltersData::entitiesFrom($creators));

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

        $crawler = self::$client->request('GET', '/htmx/main/creators-in-table?'.$query);
        self::assertResponseStatusCodeIs(200);

        $resultCreatorIds = $crawler->filter('td.creator-id')->each(fn (Crawler $node, $_) => $node->text(''));

        self::assertArrayItemsSameOrderIgnored($expectedCreatorIds, $resultCreatorIds, "$query query failed.");
    }
}
