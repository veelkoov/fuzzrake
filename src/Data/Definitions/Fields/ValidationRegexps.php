<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use App\Utils\Traits\UtilityClass;

final class ValidationRegexps
{
    use UtilityClass;

    /* TODO: Improve URL validation regexps #79 */
    public const string GENERIC_URL = '~^(https?://[^/]+/.*)?$~n';
    public const string DA_URL = '~^(https://www\.deviantart\.com/[^/]+|https://[^.]+\.deviantart\.com/)?$~n';
    public const string FACEBOOK_URL = '~^(https://www.facebook.com/([^/]+/|profile\.php\?id=\d+))?$~n';
    public const string FSR_URL = '~^(https://fursuitreview.com/m/[^/]+/)?$~n';
    public const string FA_URL = '~^(https://(www|sfw)\.furaffinity\.net/user/[^/]+/)?$~n';
    public const string YOUTUBE_URL = '~^(https://www\.youtube\.com/(((channel|user|c)/)?[^/?]+)|(@[a-zA-Z]+))?$~n';
    public const string INSTAGRAM_URL = '~^(https://www\.instagram\.com/[^/]+/)?$~n';
    /* FIXME: Allow custom-domain exceptions. Improve URL validation regexps #79 */
    public const string TUMBLR_URL = '~^((https://[^.]+\.tumblr\.com/)|(https://www\.tumblr\.com/[^/?]+)|(https://pupdates.matrices.net/))?$~n';
    public const string TWITTER_URL = '~^(https://twitter\.com/[^/]+)?$~n';
    public const string SCRITCH_URL = '~^(https://scritch\.es/(makers/)?[-a-zA-Z0-9]+)?$~n';
    public const string FURTRACK_URL = '~^(https://www\.furtrack\.com/(index/maker:|user/)[a-zA-Z0-9_]+)?$~n';
    public const string TIKTOK_URL = '~^https://www\.tiktok\.com/@[a-z0-9_.]$~n';
    public const string BLUESKY_URL = '~^https://bsky\.app/profile/[^/?#]+$~n';
    public const string DONATIONS_URL = '~^https://(www\.patreon\.com|ko-fi\.com)/[^/?#]+$~n';
    public const string TELEGRAM_CHANNEL_URL = '~^https://t\.me/[^/?#]+$~n';

    /* TODO: Be more specific, separate lists? */
    public const string LIST_VALIDATION = '~^[-,&!.A-Za-z0-9+()/\n %:"\'@]*$~n';
    public const string FORMER_CREATOR_IDS = '~^([A-Z0-9]{7}(\n[A-Z0-9]{7})*)?$~n';
    public const string NON_EMPTY = '~.~n';
    public const string SINCE = '~^(\d{4}-\d{2})?$~n';
    public const string COUNTRY = '~^([A-Z]{2})?$~n';
    public const string STATE = '~^(Alabama|Alaska|Alberta|Arizona|Arkansas|British Columbia|California|Colorado|Connecticut|Delaware|Florida|Georgia|Hawaii|Idaho|Illinois|Indiana|Iowa|Kansas|Kentucky|Louisiana|Maine|Manitoba|Maryland|Massachusetts|Michigan|Minnesota|Mississippi|Missouri|Montana|Nebraska|Nevada|New Brunswick|New Hampshire|New Jersey|New Mexico|New York|Newfoundland|North Carolina|North Dakota|Nova Scotia|Ohio|Oklahoma|Ontario|Oregon|Pennsylvania|Prince Edward Island|Quebec|Rhode Island|Saskatchewan|South Carolina|South Dakota|Tennessee|Texas|Utah|Vermont|Virginia|Washington|West Virginia|Wisconsin|Wyoming)?$~n';

    public const string CURRENCIES = '~^([A-Z]{3}(\n[A-Z]{3})*)?$~n';
    private const string PAY_COMMENT = '( \([^)]+\))?';
    private const string PAY_METHODS_LIST = 'Apple Pay|Bank transfers|BLIK'
        .'|Cash|Cash App|Checks|Cashier\'s checks|Crypto'
        .'|Credit/debit cards|Credit cards|Debit cards'
        .'|Depop|Etsy Payments|E-transfers|Google Pay|Interac e-Transfer|Ko-fi|Money orders|Other'
        .'|PayID|PayPal|Revolut|SEPA|Square|Stripe|Swish|Venmo|Vipps|VISA|Wise|Zelle';
    public const string PAY_METHODS = '~^((?<=\n|^)('.self::PAY_METHODS_LIST.')'.self::PAY_COMMENT.'(\n|(?=$)))*$~n';

    /* TODO: Improve URL validation regexps #79 */
    public const string GENERIC_URL_LIST = '~^(https?://[^/]+/.*(\nhttps?://[^/]+/.*)*)?$~n';
    public const string PHOTO_URL_LIST = '~^((https://scritch\.es/pictures/[-a-f0-9]{36}|https://www\.furtrack\.com/p/\d+)(\n(https://scritch\.es/pictures/[-a-f0-9]{36}|https://www\.furtrack\.com/p/\d+)){0,4})?$~n';
    /* TODO: Improve URL validation regexps #79 */
    public const string MINIATURE_URL_LIST = '~.?~n';
}
