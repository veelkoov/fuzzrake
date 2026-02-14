<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web\UrlStrategy;

use App\Utils\Web\UrlStrategy\FurAffinityProfileStrategy;
use App\Utils\Web\UrlStrategy\InstagramProfileStrategy;
use App\Utils\Web\UrlStrategy\Strategies;
use App\Utils\Web\UrlStrategy\TrelloStrategy;
use App\Utils\Web\UrlStrategy\TwitterProfileStrategy;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class StrategiesTest extends TestCase
{
    public function testTwitterProfileStrategyIsNotUsedForCookieInitUrl(): void
    {
        $subject = Strategies::getFor(new TwitterProfileStrategy()->getCookieInitUrl()->getUrl());

        self::assertNull($subject->getCookieInitUrl());
    }

    public function testTwitterProfileStrategyGetsUsedWhenSupposed(): void
    {
        self::assertInstanceOf(
            TwitterProfileStrategy::class,
            Strategies::getFor('https://twitter.com/getfursuit'),
        );
        self::assertNotInstanceOf(
            TwitterProfileStrategy::class,
            Strategies::getFor('https://twitter.com/getfursuit?s=09'),
            'Fuzzrake expects precise URL matching.',
        );
    }

    public function testInstagramProfileStrategyGetsUsedWhenSupposed(): void
    {
        self::assertInstanceOf(
            InstagramProfileStrategy::class,
            Strategies::getFor('https://www.instagram.com/getfursu.it/'),
        );
        self::assertInstanceOf(
            InstagramProfileStrategy::class,
            Strategies::getFor('https://www.instagram.com/getfursu.it'),
        );
        self::assertNotInstanceOf(
            InstagramProfileStrategy::class,
            Strategies::getFor('https://www.instagram.com/p/Ct6gnaEtZtJ/'),
        );
        self::assertNotInstanceOf(
            InstagramProfileStrategy::class,
            Strategies::getFor('https://www.instagram.com/s/aAaAaAaAaAaAaAaAaAaAaAaAaAaAaAaAaAaA'),
        );
    }

    public function testTrelloStrategyGetsUsed(): void
    {
        self::assertInstanceOf(
            TrelloStrategy::class,
            Strategies::getFor('https://trello.com/b/aBcDeFgHi/some-test-name'),
        );
        self::assertInstanceOf(
            TrelloStrategy::class,
            Strategies::getFor('https://trello.com/b/aBcDeFgHi'),
        );
        self::assertInstanceOf(
            TrelloStrategy::class,
            Strategies::getFor('https://trello.com/b/aBcDeFgHi/'),
        );
        self::assertInstanceOf(
            TrelloStrategy::class,
            Strategies::getFor('https://trello.com/c/aBcDeFgHi/some-test-description'),
        );
        self::assertInstanceOf(
            TrelloStrategy::class,
            Strategies::getFor('https://trello.com/c/aBcDeFgHi'),
        );
        self::assertInstanceOf(
            TrelloStrategy::class,
            Strategies::getFor('https://trello.com/c/aBcDeFgHi/'),
        );
    }

    public function testFurAffinityProfileStrategyGetsUsed(): void
    {
        self::assertInstanceOf(
            FurAffinityProfileStrategy::class,
            Strategies::getFor('http://www.furaffinity.net/user/lisoov/#profile'),
        );
        self::assertInstanceOf(
            FurAffinityProfileStrategy::class,
            Strategies::getFor('https://www.furaffinity.net/user/lisoov/'),
        );
        self::assertInstanceOf(
            FurAffinityProfileStrategy::class,
            Strategies::getFor('https://www.furaffinity.net/user/lisoov#profile/'),
        );
        self::assertInstanceOf(
            FurAffinityProfileStrategy::class,
            Strategies::getFor('https://www.furaffinity.net/user/lisoov'),
        );
        self::assertNotInstanceOf(
            FurAffinityProfileStrategy::class,
            Strategies::getFor('https://www.furaffinity.net/journal/0000000/'),
        );
    }
}
