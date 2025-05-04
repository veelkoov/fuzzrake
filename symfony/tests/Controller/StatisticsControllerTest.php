<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Data\Definitions\Features;
use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\OrderTypes;
use App\Data\Definitions\ProductionModels;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use PHPUnit\Framework\Attributes\Medium;
use Symfony\Component\DomCrawler\Crawler;

#[Medium]
class StatisticsControllerTest extends FuzzrakeWebTestCase
{
    public function testStatisticsPageLoads(): void
    {
        self::persistAndFlush(self::getCreator()
            ->setOtherFeatures(['Smoke detector'])
            ->setSpeciesDoes(['Wolves'])
            ->setSpeciesDoesnt(['Coyotes'])
        );

        self::$client->request('GET', '/stats');

        self::assertResponseStatusCodeIs(200);
        self::assertSelectorTextContains('h1#data-statistics', 'Data statistics');
    }

    public function testInactiveCreatorsDontCount(): void
    {
        $a1 = self::getCreator('A1', 'TEST0041')
            ->setFeatures([Features::FOLLOW_ME_EYES])
            ->setProductionModels([ProductionModels::STANDARD_COMMISSIONS])
            ->setCountry('CZ')
        ;
        $a2 = self::getCreator('A2', 'TEST0042')
            ->setFeatures([Features::FOLLOW_ME_EYES])
            ->setOrderTypes([OrderTypes::FULL_DIGITIGRADE])
            ->setCountry('SK')
        ;
        $a3 = self::getCreator('A3', 'TEST0043')
            ->setProductionModels([ProductionModels::STANDARD_COMMISSIONS])
            ->setInactiveReason('Hidden')
            ->setCountry('IT')
        ;

        self::persistAndFlush($a1, $a2, $a3);

        self::$client->request('GET', '/stats');
        $crawler = self::$client->getCrawler();

        self::assertResponseStatusCodeIs(200);
        self::assertRowValueEquals('2 (100.00%)', Features::FOLLOW_ME_EYES, $crawler);
        self::assertRowValueEquals('1 (50.00%)', ProductionModels::STANDARD_COMMISSIONS, $crawler);
        self::assertRowValueEquals('1 (50.00%)', OrderTypes::FULL_DIGITIGRADE, $crawler);
        self::assertRowValueEquals('2 (100.00%)', 'Unknown', $crawler->filterXPath('//h1[text()="Styles"]')->nextAll()->first());
        self::assertRowValueEquals('2 (100.00%)', 'Total', $crawler->filterXPath('//h1[text()="Commission status"]')->nextAll()->first());
        self::assertRowValueEquals('2 (100.00%)', Field::NAME->value, $crawler);
        self::assertRowValueEquals('1 (50.00% × 2 = 100.00%)', 'Czechia, Slovakia', $crawler);
        // https://github.com/veelkoov/fuzzrake/issues/74
        // static::assertRowValueEquals('1 (50.00%)', '40-49%', $crawler);
        // static::assertRowValueEquals('1 (50.00%)', '50-59%', $crawler);
    }

    public function testFakeFormerCreatorIdsDontCount(): void
    {
        $creatorOnlyFakeId = new Creator();
        $creatorFakeIdAndNew = new Creator();
        $creatorFakeIdAndOldAndNew = new Creator();

        self::persistAndFlush($creatorFakeIdAndNew, $creatorOnlyFakeId, $creatorFakeIdAndOldAndNew);

        $creatorOnlyFakeId->setCreatorId('')->setFormerCreatorIds([sprintf('M%06d', $creatorOnlyFakeId->getId())]);
        $creatorFakeIdAndNew->setCreatorId('TEST001')
            ->setFormerCreatorIds([sprintf('M%06d', $creatorFakeIdAndNew->getId())]);
        $creatorFakeIdAndOldAndNew->setCreatorId('TEST002')
            ->setFormerCreatorIds([sprintf("TEST003\nM%06d", $creatorFakeIdAndOldAndNew->getId())]);

        $creator3 = (new Creator())->setCreatorId('TEST004')->setFormerCreatorIds(['TEST005', 'TEST006']);
        $creator4 = (new Creator())->setCreatorId('TEST007')->setFormerCreatorIds(['TEST008']);
        $creator5 = (new Creator())->setCreatorId('TEST009')->setFormerCreatorIds([]);

        self::persistAndFlush($creator3, $creator4, $creator5);

        self::$client->request('GET', '/stats');
        $crawler = self::$client->getCrawler();

        self::assertSame('3 (50.00%)', self::getRowValue($crawler, 'FORMER_MAKER_IDS'));
    }

    private static function assertRowValueEquals(string $expected, string $rowLabel, Crawler $crawler): void
    {
        self::assertSame($expected, self::getRowValue($crawler, $rowLabel));
    }

    private static function getRowValue(Crawler $crawler, string $rowLabel): string
    {
        return $crawler->filter('td:contains("'.$rowLabel.'")')->siblings()->text();
    }
}
