<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\FiltersTestTrait;
use App\Tests\TestUtils\FiltersData;
use App\Tests\TestUtils\UserCreator;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\DomCrawler\Crawler;

#[Medium]
class MainControllerTest extends FuzzrakeWebTestCase
{
    use ClockSensitiveTrait;
    use FiltersTestTrait;

    /**
     * @param list<Creator>                    $creators
     * @param array<string, list<string>|bool> $filtersSet
     * @param list<string>                     $expectedCreatorIds
     */
    #[DataProvider('filterChoicesDataProvider')]
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

        self::$client->request('GET', '/htmx/main/creators-in-table?'.$query);
        self::assertResponseStatusCodeIs(200);

        $resultCreatorIds = $this->getCrawlerForPartialTableHtml(self::$client->getResponse()->getContent())
            ->filter('td.creator-id')->each(static fn (Crawler $node, $_) => $node->text(''));

        self::assertSameItems($expectedCreatorIds, $resultCreatorIds, "$query query failed.");
    }

    private function getCrawlerForPartialTableHtml(string|false $responseContent): Crawler
    {
        self::assertIsString($responseContent);

        return new Crawler('<!DOCTYPE html> <html lang="en"> <table>'.$responseContent.'</table></html>');
    }

    public function testMainPageLoads(): void
    {
        self::persistAndFlush(UserCreator::get(true));

        self::$client->request('GET', '/');

        self::assertResponseStatusCodeIs(200);
        self::assertSelectorTextContains('#main-page-intro h4', 'Fursuit makers database');
    }

    /**
     * @throws DateTimeException
     */
    public function testRecentlyAddedPage(): void
    {
        self::mockTime();

        $creator1 = UserCreator::get()->setCreatorId('TEST001')->setName('Older creator 1')->setDateAdded(UtcClock::at('-43 days'));
        $creator2 = UserCreator::get()->setCreatorId('TEST002')->setName('Newer creator 2')->setDateAdded(UtcClock::at('-41 days'));
        $creator3 = UserCreator::get()->setCreatorId('TEST003')->setName('Newer creator 3')->setDateAdded(UtcClock::at('-40 days'))
            ->setFormerly(['Formerly 3A', 'Formerly 3B']);

        self::persistAndFlush($creator1, $creator2, $creator3);
        $this->clearCache();

        $crawler = self::$client->request('GET', '/new');
        self::assertResponseStatusCodeIs(200);

        self::assertEmpty($crawler->filterXPath('//li/a[text() = "Older creator 1"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/a[text() = "Newer creator 2"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/a[text() = "Newer creator 3"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/span[normalize-space(text()) = "/ Formerly 3A / Formerly 3B"]'));
    }
}
