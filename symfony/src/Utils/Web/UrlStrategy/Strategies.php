<?php

declare(strict_types=1);

namespace App\Utils\Web\UrlStrategy;

use App\Utils\Traits\UtilityClass;

final class Strategies
{
    use UtilityClass;

    public static function getFor(string $url): Strategy
    {
        return match (true) {
            TwitterProfileStrategy::isSuitableFor($url) => new TwitterProfileStrategy(),
            InstagramProfileStrategy::isSuitableFor($url) => new InstagramProfileStrategy(),
            TrelloStrategy::isSuitableFor($url) => new TrelloStrategy(),
            FurAffinityProfileStrategy::isSuitableFor($url) => new FurAffinityProfileStrategy(),
            default => new GenericStrategy(),
        };
    }
}
