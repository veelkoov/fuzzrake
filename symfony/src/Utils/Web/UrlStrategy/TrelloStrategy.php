<?php

declare(strict_types=1);

namespace App\Utils\Web\UrlStrategy;

use App\Utils\Regexp\Patterns;
use App\Utils\Web\Url\Url;
use App\Utils\Web\Url\UrlForTracking;
use Override;

class TrelloStrategy extends Strategy
{
    private const string trelloBoardOrCardUrl = '^https://trello\.com/(?<type>[bc])/(?<id>[^/]+)(/.*)?$';

    #[Override]
    public static function isSuitableFor(string $url): bool
    {
        return Patterns::get(self::trelloBoardOrCardUrl)->test($url);
    }

    #[Override]
    public function getUrlForTracking(Url $url): Url
    {
        $match = Patterns::get(self::trelloBoardOrCardUrl)->match($url->getUrl());

        if (!$match->test()) {
            return $url;
        }

        $detail = $match->first();

        if ('b' === $detail->get('type')) {
            return new UrlForTracking("https://trello.com/1/boards/{$detail->get('id')}?fields=name%2Cdesc&cards=visible&card_fields=name%2Cdesc", $url);
        } else {
            return new UrlForTracking("https://trello.com/1/cards/{$detail->get('id')}?fields=name%2Cdesc", $url);
        }
    }
}
