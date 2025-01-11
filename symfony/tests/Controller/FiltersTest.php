<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\Traits\AssertsTrait;
use App\Tests\TestUtils\Cases\Traits\FiltersTestTrait;
use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use App\Tests\TestUtils\FiltersData;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Enforce;
use DOMElement;
use Facebook\WebDriver\WebDriverElement;
use JsonException;
use LogicException;

/**
 * @medium
 */
class FiltersTest extends WebTestCaseWithEM
{
    use AssertsTrait;
    use FiltersTestTrait;

    /**
     * @dataProvider filterChoicesDataProvider
     *
     * @param list<Artisan>                    $artisans
     * @param array<string, list<string>|bool> $filtersSet
     * @param list<string>                     $expectedMakerIds
     *
     * @throws JsonException
     */
    public function testFiltersThroughHtmx(array $artisans, array $filtersSet, array $expectedMakerIds): void
    {
        $client = static::createClient();

        self::persistAndFlush(...$artisans, ...FiltersData::entitiesFrom($artisans));

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

        $crawler = $client->request('GET', '/htmx/main/creators-in-table?'.$query);
        self::assertResponseStatusCodeIs($client, 200);

        $resultMakerIds = [];

        foreach ($crawler->filter('td.makerId') as $node) {
            $resultMakerIds[] = trim(Enforce::objectOf($node, DOMElement::class)->nodeValue
                ?? throw new LogicException('Missing node value'));
        }

        self::assertArrayItemsSameOrderIgnored($expectedMakerIds, $resultMakerIds, "$query query failed.");
    }
}
