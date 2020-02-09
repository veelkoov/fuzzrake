<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

abstract class ValidationRegexps
{
    public const MAKER_ID = '#^([A-Z0-9]{7})?$#';
    public const GENERIC_URL = '#^(https?://[^/]+/.*)?$#'; // TODO: improve
    public const DA_URL = '#^(https://www\.deviantart\.com/[^/]+|https://[^.]+\.deviantart\.com/)?$#';
    public const FACEBOOK_URL = '#^(https://www.facebook.com/([^/]+/|profile\.php\?id=\d+))?$#';
    public const FSR_URL = '#^(http://fursuitreview.com/maker/[^/]+/)?$#';
    public const FA_URL = '#^(http://www\.furaffinity\.net/user/[^/]+)?$#';
    public const YOUTUBE_URL = '#^(https://www\.youtube\.com/((channel|user|c)/)?[^/?]+)?$#';
    public const INSTAGRAM_URL = '#^(https://www\.instagram\.com/[^/]+/)?$#';
    public const TUMBLR_URL = '#^((https?://[^.]+\.tumblr\.com/)|(https://pupdates.matrices.net/))?$#'; // FIXME: Allow custom-domain exceptions
    public const TWITTER_URL = '#^(https://twitter\.com/[^/]+)?$#';

    public const LIST_VALIDATION = '#^[-,&!.A-Za-z0-9+()/\n %:"\']*$#';
    public const FORMER_MAKER_IDS = '#^([A-Z0-9]{7}(\n[A-Z0-9]{7})*)?$#';
    public const ANYTHING = '#^.*$#s';
    public const NON_EMPTY = '#^.+$#';
    public const SINCE = '#^(\d{4}-\d{2})?$#';
    public const COUNTRY = '#^([A-Z]{2})?$#';

    public const SCRITCH_PHOTO_URLS = self::ANYTHING; // FIXME
    public const SCRITCH_MINIATURE_URLS = self::ANYTHING; // FIXME
    public const SCRITCH_URL = self::ANYTHING; // FIXME
}
