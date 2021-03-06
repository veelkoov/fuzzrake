<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\Utils\Traits\UtilityClass;

final class ValidationRegexps
{
    use UtilityClass;

    public const MAKER_ID = '^([A-Z0-9]{7})?$';

    public const GENERIC_URL = '^(https?://[^/]+/.*)?$'; // TODO: improve
    public const DA_URL = '^(https://www\.deviantart\.com/[^/]+|https://[^.]+\.deviantart\.com/)?$';
    public const FACEBOOK_URL = '^(https://www.facebook.com/([^/]+/|profile\.php\?id=\d+))?$';
    public const FSR_URL = '^(https://fursuitreview.com/m/[^/]+/)?$';
    public const FA_URL = '^(https://www\.furaffinity\.net/user/[^/]+/)?$';
    public const YOUTUBE_URL = '^(https://www\.youtube\.com/((channel|user|c)/)?[^/?]+)?$';
    public const INSTAGRAM_URL = '^(https://www\.instagram\.com/[^/]+/)?$';
    public const TUMBLR_URL = '^((https?://[^.]+\.tumblr\.com/)|(https://pupdates.matrices.net/))?$'; // FIXME: Allow custom-domain exceptions
    public const TWITTER_URL = '^(https://twitter\.com/[^/]+)?$';
    public const SCRITCH_URL = '^(https://scritch\.es/(makers/)?[-a-zA-Z0-9]+)?$';
    public const FURTRACK_URL = '^(https://www\.furtrack\.com/index/maker:[a-z0-9_]+)?$';

    public const LIST_VALIDATION = '^[-,&!.A-Za-z0-9+()/\n %:"\']*$';
    public const FORMER_MAKER_IDS = '^([A-Z0-9]{7}(\n[A-Z0-9]{7})*)?$';
    public const ANYTHING = '.?';
    public const NON_EMPTY = '.';
    public const SINCE = '^(\d{4}-\d{2})?$';
    public const COUNTRY = '^([A-Z]{2})?$';

    public const PHOTO_URL_LIST = '^((https://scritch\.es/pictures/[-a-f0-9]{36}|https://www\.furtrack\.com/p/\d+)(\n(https://scritch\.es/pictures/[-a-f0-9]{36}|https://www\.furtrack\.com/p/\d+)){0,4})?$';
    public const MINIATURE_URL_LIST = self::ANYTHING; // TODO
}
