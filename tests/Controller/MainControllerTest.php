<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use PHPUnit\Framework\Attributes\Medium;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

#[Medium]
class MainControllerTest extends FuzzrakeWebTestCase
{
    use ClockSensitiveTrait;

    public function testMainPageLoads(): void
    {
        self::addSimpleCreator();

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

        $creator1 = new Creator()->setCreatorId('TEST001')->setName('Older creator')->setDateAdded(UtcClock::at('-43 days'));
        $creator2 = new Creator()->setCreatorId('TEST002')->setName('Newer creator 1')->setDateAdded(UtcClock::at('-41 days'));
        $creator3 = new Creator()
            ->setCreatorId('TEST003')
            ->setName('Newer creator 2')
            ->setFormerly(['Formerly 2a', 'Formerly 2b'])
            ->setDateAdded(UtcClock::at('-40 days'));

        self::persistAndFlush($creator1, $creator2, $creator3);
        $this->clearCache();

        $crawler = self::$client->request('GET', '/new');
        self::assertResponseStatusCodeIs(200);

        self::assertEmpty($crawler->filterXPath('//li/a[text() = "Older creator"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/a[text() = "Newer creator 1"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/a[text() = "Newer creator 2"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/span[normalize-space(text()) = "/ Formerly 2a / Formerly 2b"]'));
    }
}
