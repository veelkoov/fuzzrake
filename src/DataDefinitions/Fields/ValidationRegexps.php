<?php

declare(strict_types=1);

namespace App\DataDefinitions\Fields;

use App\Utils\Traits\UtilityClass;

final class ValidationRegexps
{
    use UtilityClass;

    final public const MAKER_ID = '^([A-Z0-9]{7})?$';

    final public const GENERIC_URL = '^(https?://[^/]+/.*)?$'; // TODO: Improve URL validation regexps #79
    final public const DA_URL = '^(https://www\.deviantart\.com/[^/]+|https://[^.]+\.deviantart\.com/)?$';
    final public const FACEBOOK_URL = '^(https://www.facebook.com/([^/]+/|profile\.php\?id=\d+))?$';
    final public const FSR_URL = '^(https://fursuitreview.com/m/[^/]+/)?$';
    final public const FA_URL = '^(https://www\.furaffinity\.net/user/[^/]+/)?$';
    final public const YOUTUBE_URL = '^(https://www\.youtube\.com/((channel|user|c)/)?[^/?]+)?$';
    final public const INSTAGRAM_URL = '^(https://www\.instagram\.com/[^/]+/)?$';
    final public const TUMBLR_URL = '^((https?://[^.]+\.tumblr\.com/)|(https://pupdates.matrices.net/))?$'; // FIXME: Allow custom-domain exceptions. Improve URL validation regexps #79
    final public const TWITTER_URL = '^(https://twitter\.com/[^/]+)?$';
    final public const SCRITCH_URL = '^(https://scritch\.es/(makers/)?[-a-zA-Z0-9]+)?$';
    final public const FURTRACK_URL = '^(https://www\.furtrack\.com/(index/maker:|user/)[a-zA-Z0-9_]+)?$';

    final public const LIST_VALIDATION = '^[-,&!.A-Za-z0-9+()/\n %:"\'@]*$'; // TODO: Be more specific, separate lists?
    final public const FORMER_MAKER_IDS = '^([A-Z0-9]{7}(\n[A-Z0-9]{7})*)?$';
    final public const NON_EMPTY = '.';
    final public const SINCE = '^(\d{4}-\d{2})?$';
    final public const COUNTRY = '^([A-Z]{2})?$';
    final public const STATE = '^([A-Za-zé ]{4,})?$';

    final public const CURRENCIES = '^([A-Z]{3}(\n[A-Z]{3})*)?$';
    private const PAY_COMMENT = '( \([^)]+\))?';
    private const PAY_METHODS_LIST = 'Apple Pay|Bank transfers|BLIK'
        .'|Cash|Cash App|Checks|Cashier\'s checks|Crypto'
        .'|Credit/debit cards'.'|Credit cards'.'|Debit cards'
        .'|Depop|Etsy Payments|E-transfers|Google Pay|Interac e-Transfer|Ko-fi|Money orders|Other'
        .'|PayID|PayPal|Revolut|SEPA|Square|Stripe|Swish|Venmo|Vipps|VISA|Wise|Zelle';
    final public const PAY_METHODS = '^((?<=\n|^)('.self::PAY_METHODS_LIST.')'.self::PAY_COMMENT.'(\n|(?=$)))*$';

    final public const GENERIC_URL_LIST = '^(https?://[^/]+/.*(\nhttps?://[^/]+/.*)*)?$'; // TODO: Improve URL validation regexps #79
    final public const PHOTO_URL_LIST = '^((https://scritch\.es/pictures/[-a-f0-9]{36}|https://www\.furtrack\.com/p/\d+)(\n(https://scritch\.es/pictures/[-a-f0-9]{36}|https://www\.furtrack\.com/p/\d+)){0,4})?$';
    final public const MINIATURE_URL_LIST = '.?'; // TODO: Improve URL validation regexps #79
}
