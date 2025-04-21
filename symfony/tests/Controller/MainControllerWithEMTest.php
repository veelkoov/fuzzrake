<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\TestUtils\UtcClockMock;

/**
 * @medium
 */
class MainControllerWithEMTest extends WebTestCaseWithEM
{
    public function testMainPageLoads(): void
    {
        $client = static::createClient();
        self::addSimpleCreator();

        $client->request('GET', '/');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
        static::assertSelectorTextContains('#main-page-intro h4', 'Fursuit makers database');
    }

    /**
     * @throws DateTimeException
     */
    public function testRecentlyAddedPage(): void
    {
        $client = static::createClient();
        UtcClockMock::start();

        $creator1 = Creator::new()->setCreatorId('TEST001')->setName('Older creator')->setDateAdded(UtcClock::at('-43 days'));
        $creator2 = Creator::new()->setCreatorId('TEST002')->setName('Newer creator 1')->setDateAdded(UtcClock::at('-41 days'));
        $creator3 = Creator::new()
            ->setCreatorId('TEST003')
            ->setName('Newer creator 2')
            ->setFormerly(['Formerly 2a', 'Formerly 2b'])
            ->setDateAdded(UtcClock::at('-40 days'));

        self::persistAndFlush($creator1, $creator2, $creator3);
        $this->clearCache();

        $crawler = $client->request('GET', '/new');
        self::assertResponseStatusCodeIs($client, 200);

        self::assertEmpty($crawler->filterXPath('//li/a[text() = "Older creator"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/a[text() = "Newer creator 1"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/a[text() = "Newer creator 2"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/span[normalize-space(text()) = "/ Formerly 2a / Formerly 2b"]'));
    }
}
