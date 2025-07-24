<?php

declare(strict_types=1);

namespace App\Utils\Web\UrlStrategy;

use App\Utils\Web\Url\Url;
use Composer\Pcre\Preg;
use Override;
use Symfony\Component\DomCrawler\Crawler;

class FurAffinityProfileStrategy extends Strategy
{
    private const string FA_SYSTEM_ERROR_CONTENTS_SEARCH_STRING = '<title>System Error</title>';
    private const string FA_USER_NOT_FOUND_CONTENTS_SEARCH_STRING = 'This user cannot be found.';
    private const string FA_USER_PROFILE_REGISTERED_ONLY_SEARCH_STRING = '<div class="redirect-message">'
        .'<p class="link-override">The owner of this page has elected to make it available to registered users only.';

    private const string profileUrlRegex = '~^https?://(www\.)?furaffinity\.net/user/(?<username>[^/]+)/?([#?].*)?$~';

    #[Override]
    public static function isSuitableFor(string $url): bool
    {
        return Preg::isMatch(self::profileUrlRegex, $url);
    }

    #[Override]
    public function filterContents(string $input): string
    {
        $input = parent::filterContents($input);

        $element = new Crawler($input)->filter('#page-userpage div.userpage-profile');

        if (0 !== $element->count()) {
            return $element->html();
        } else {
            return $input;
        }
    }

    #[Override]
    public function getLatentCode(Url $url, string $contents, int $originalCode): int
    {
        if (200 !== $originalCode) {
            return $originalCode;
        }

        if (str_contains($contents, self::FA_USER_NOT_FOUND_CONTENTS_SEARCH_STRING)
            && str_contains($contents, self::FA_SYSTEM_ERROR_CONTENTS_SEARCH_STRING)
        ) {
            return 404;
        }

        if (str_contains($contents, self::FA_USER_PROFILE_REGISTERED_ONLY_SEARCH_STRING)) {
            return 401;
        }

        return $originalCode;
    }
}
