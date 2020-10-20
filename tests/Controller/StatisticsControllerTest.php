<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Utils\Artisan\Features;
use App\Utils\Artisan\Fields;
use App\Utils\Artisan\OrderTypes;
use App\Utils\Artisan\ProductionModels;
use Symfony\Component\DomCrawler\Crawler;

class StatisticsControllerTest extends DbEnabledWebTestCase
{
    public function testStatisticsPageLoads(): void
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/statistics.html');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('h1#data_statistics', 'Data statistics');
    }

    public function testInactiveMakersDontCount(): void
    {
        $client = static::createClient();

        $a1 = self::getArtisan('A1', 'AAAAAAA1')
            ->setFeatures(Features::FOLLOW_ME_EYES)
            ->setProductionModels(ProductionModels::STANDARD_COMMISSIONS)
            ->setCountry('CZ')
        ;
        $a2 = self::getArtisan('A2', 'AAAAAAA2')
                ->setFeatures(Features::FOLLOW_ME_EYES)
            ->setOrderTypes(OrderTypes::FULL_DIGITIGRADE)
            ->setCountry('SK')
        ;
        $a3 = self::getArtisan('A3', 'AAAAAAA3')
            ->setProductionModels(ProductionModels::STANDARD_COMMISSIONS)
            ->setInactiveReason('Inactive')
            ->setCountry('IT')
        ;

        self::persistAndFlush($a1, $a2, $a3);

        $client->request('GET', '/statistics.html');
        $crawler = $client->getCrawler();

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertRowValueEquals('2 (100.00%)', Features::FOLLOW_ME_EYES, $crawler);
        static::assertRowValueEquals('1 (50.00%)', ProductionModels::STANDARD_COMMISSIONS, $crawler);
        static::assertRowValueEquals('1 (50.00%)', OrderTypes::FULL_DIGITIGRADE, $crawler);
        static::assertRowValueEquals('2 (100.00%)', 'Unknown', $crawler->filterXPath('//h1[text()="Styles"]')->nextAll()->first());
        static::assertRowValueEquals('2 (100.00%)', 'Total', $crawler->filterXPath('//h1[text()="Commission status"]')->nextAll()->first());
        static::assertRowValueEquals('2 (100.00%)', Fields::NAME, $crawler);
        static::assertRowValueEquals('1 (50.00% × 2 = 100.00%)', 'CZ, SK', $crawler);
        static::assertRowValueEquals('2 (100.00%)', '40-49%', $crawler);
    }

    private static function assertRowValueEquals(string $expected, string $rowLabel, Crawler $crawler): void
    {
        static::assertEquals($expected, static::getRowValue($crawler, $rowLabel));
    }

    private static function getRowValue(Crawler $crawler, string $rowLabel): string
    {
        return $crawler->filter('td:contains("'.$rowLabel.'")')->siblings()->text();
    }
}
