<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\UserCreator;
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
        self::persistAndFlushWithUsers(
            UserCreator::get()
                ->setName('Test creator')
                ->setCreatorId('TEST000')
                ->setCountry('FI')
        );

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

        self::persistAndFlushWithUsers($creator1, $creator2, $creator3);
        $this->clearCache();

        $crawler = self::$client->request('GET', '/new');
        self::assertResponseStatusCodeIs(200);

        self::assertEmpty($crawler->filterXPath('//li/a[text() = "Older creator 1"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/a[text() = "Newer creator 2"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/a[text() = "Newer creator 3"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/span[normalize-space(text()) = "/ Formerly 3A / Formerly 3B"]'));
    }
}
