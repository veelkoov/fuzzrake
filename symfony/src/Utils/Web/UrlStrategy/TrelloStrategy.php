<?php

declare(strict_types=1);

namespace App\Utils\Web\UrlStrategy;

use App\Utils\Web\Url\Url;
use App\Utils\Web\Url\UrlForTracking;
use Composer\Pcre\Regex;
use Override;

class TrelloStrategy extends Strategy
{
    private const string trelloBoardOrCardUrl = '~^https://trello\.com/(?<type>[bc])/(?<id>[^/]+)(/.*)?$~n';

    #[Override]
    public static function isSuitableFor(string $url): bool
    {
        return Regex::isMatch(self::trelloBoardOrCardUrl, $url);
    }

    #[Override]
    public function getUrlForTracking(Url $url): Url
    {
        $match = Regex::matchStrictGroups(self::trelloBoardOrCardUrl, $url->getUrl());

        if (!$match->matched) {
            return $url;
        }

        if ('b' === $match->matches['type']) {
            return new UrlForTracking("https://trello.com/1/boards/{$match->matches['id']}?fields=name%2Cdesc&cards=visible&card_fields=name%2Cdesc", $url);
        } else {
            return new UrlForTracking("https://trello.com/1/cards/{$match->matches['id']}?fields=name%2Cdesc", $url);
        }
    }
}
