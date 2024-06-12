<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
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
        self::addSimpleArtisan();

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

        $maker1 = Artisan::new()->setMakerId('MAKER01')->setName('Older maker')->setDateAdded(UtcClock::at('-43 days'));
        $maker2 = Artisan::new()->setMakerId('MAKER02')->setName('Newer maker 1')->setDateAdded(UtcClock::at('-41 days'));
        $maker3 = Artisan::new()
            ->setMakerId('MAKER03')
            ->setName('Newer maker 2')
            ->setFormerly(['Formerly 2a', 'Formerly 2b'])
            ->setDateAdded(UtcClock::at('-40 days'));

        self::persistAndFlush($maker1, $maker2, $maker3);
        $this->clearCache();

        $crawler = $client->request('GET', '/new');
        self::assertResponseStatusCodeIs($client, 200);

        self::assertEmpty($crawler->filterXPath('//li/a[text() = "Older maker"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/a[text() = "Newer maker 1"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/a[text() = "Newer maker 2"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/span[normalize-space(text()) = "/ Formerly 2a / Formerly 2b"]'));
    }
}
