<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use App\Data\Definitions\Fields\Field as F;
use App\Data\Definitions\Fields\ValidationRegexps as V;
use App\Utils\Traits\UtilityClass;

use function Psl\Iter\contains;

final class FieldsData
{
    use UtilityClass;

    final public const MARK_LIST = 'L';
    final public const MARK_FREE_FORM = 'A';
    final public const MARK_STATS = 'S';
    final public const MARK_PUBLIC = 'P';
    final public const MARK_IN_IU_FORM = 'F';

    final public const DATA = [
    //
    //                   is in I/U Form ---------F
    //                       is Public? -------P
    //                   show in Stats? -----S
    //         is free form (anything)? ---A
    //  PRETTY_NAME            is List? -L         model name (artisan)      validation regexp
    //
        'MAKER_ID'                  => ['  A S P F makerId',                 V::MAKER_ID],
        'FORMER_MAKER_IDS'          => ['L   S P   formerMakerIds',          V::FORMER_MAKER_IDS],
        'NAME'                      => ['  A S P F name',                    V::NON_EMPTY],
        'FORMERLY'                  => ['L A S P F formerly',                null],
        'DATE_ADDED'                => ['      P   dateAdded',               null],
        'DATE_UPDATED'              => ['      P   dateUpdated',             null],
        'INTRO'                     => ['  A S P F intro',                   null],
        'SINCE'                     => ['    S P F since',                   V::SINCE],
        'LANGUAGES'                 => ['L A S P F languages',               null],
        'COUNTRY'                   => ['    S P F country',                 V::COUNTRY],
        'STATE'                     => ['  A S P F state',                   V::STATE],
        'CITY'                      => ['  A S P F city',                    null],
        'PRODUCTION_MODELS_COMMENT' => ['  A   P F productionModelsComment', null],
        'PRODUCTION_MODELS'         => ['L   S P F productionModels',        V::LIST_VALIDATION],
        'STYLES_COMMENT'            => ['  A   P F stylesComment',           null],
        'STYLES'                    => ['L   S P F styles',                  V::LIST_VALIDATION],
        'OTHER_STYLES'              => ['L A S P F otherStyles',             V::LIST_VALIDATION],
        'ORDER_TYPES_COMMENT'       => ['  A   P F orderTypesComment',       null],
        'ORDER_TYPES'               => ['L   S P F orderTypes',              V::LIST_VALIDATION],
        'OTHER_ORDER_TYPES'         => ['L A S P F otherOrderTypes',         V::LIST_VALIDATION],
        'FEATURES_COMMENT'          => ['  A   P F featuresComment',         null],
        'FEATURES'                  => ['L   S P F features',                V::LIST_VALIDATION],
        'OTHER_FEATURES'            => ['L A S P F otherFeatures',           V::LIST_VALIDATION],
        'PAYMENT_PLANS'             => ['L A S P F paymentPlans',            null],
        'PAYMENT_METHODS'           => ['L A S P F paymentMethods',          V::PAY_METHODS],
        'CURRENCIES_ACCEPTED'       => ['L A S P F currenciesAccepted',      V::CURRENCIES],
        'SPECIES_COMMENT'           => ['  A S P F speciesComment',          null],
        'SPECIES_DOES'              => ['L A S P F speciesDoes',             ''],
        'SPECIES_DOESNT'            => ['L A S P F speciesDoesnt',           ''],
        'IS_MINOR'                  => ['      P   isMinor',                 null],
        'AGES'                      => ['    S P F ages',                    null],
        'NSFW_WEBSITE'              => ['      P F nsfwWebsite',             null],
        'NSFW_SOCIAL'               => ['      P F nsfwSocial',              null],
        'DOES_NSFW'                 => ['      P F doesNsfw',                null],
        'SAFE_DOES_NSFW'            => ['      P   safeDoesNsfw',            null],
        'WORKS_WITH_MINORS'         => ['        F worksWithMinors',         null],
        'SAFE_WORKS_WITH_MINORS'    => ['      P   safeWorksWithMinors',     null],
        'URL_FURSUITREVIEW'         => ['  A S P F fursuitReviewUrl',        V::FSR_URL],
        'URL_WEBSITE'               => ['  A S P F websiteUrl',              V::GENERIC_URL],
        'URL_PRICES'                => ['L A S P F pricesUrls',              V::GENERIC_URL_LIST],
        'URL_COMMISSIONS'           => ['L A S P F commissionsUrls',         V::GENERIC_URL_LIST],
        'URL_FAQ'                   => ['  A S P F faqUrl',                  V::GENERIC_URL],
        'URL_FUR_AFFINITY'          => ['  A S P F furAffinityUrl',          V::FA_URL],
        'URL_DEVIANTART'            => ['  A S P F deviantArtUrl',           V::DA_URL],
        'URL_MASTODON'              => ['  A S P F mastodonUrl',             V::GENERIC_URL],
        'URL_TWITTER'               => ['  A S P F twitterUrl',              V::TWITTER_URL],
        'URL_FACEBOOK'              => ['  A S P F facebookUrl',             V::FACEBOOK_URL],
        'URL_TUMBLR'                => ['  A S P F tumblrUrl',               V::TUMBLR_URL],
        'URL_INSTAGRAM'             => ['  A S P F instagramUrl',            V::INSTAGRAM_URL],
        'URL_YOUTUBE'               => ['  A S P F youtubeUrl',              V::YOUTUBE_URL],
        'URL_LINKLIST'              => ['  A S P F linklistUrl',             V::GENERIC_URL],
        'URL_FURRY_AMINO'           => ['  A S P F furryAminoUrl',           V::GENERIC_URL],
        'URL_ETSY'                  => ['  A S P F etsyUrl',                 V::GENERIC_URL],
        'URL_THE_DEALERS_DEN'       => ['  A S P F theDealersDenUrl',        V::GENERIC_URL],
        'URL_OTHER_SHOP'            => ['  A S P F otherShopUrl',            V::GENERIC_URL],
        'URL_QUEUE'                 => ['  A S P F queueUrl',                V::GENERIC_URL],
        'URL_SCRITCH'               => ['  A S P F scritchUrl',              V::SCRITCH_URL],
        'URL_FURTRACK'              => ['  A S P F furtrackUrl',             V::FURTRACK_URL],
        'URL_PHOTOS'                => ['L A S P F photoUrls',               V::PHOTO_URL_LIST],
        'URL_MINIATURES'            => ['L A S P   miniatureUrls',           V::MINIATURE_URL_LIST],
        'URL_OTHER'                 => ['  A S P F otherUrls',               null],
        'NOTES'                     => ['  A   P F notes',                   null],
        'INACTIVE_REASON'           => ['    S P   inactiveReason',          null],
        'PASSWORD'                  => ['        F password',                null],
        'CS_LAST_CHECK'             => ['      P   csLastCheck',             null],
        'CS_TRACKER_ISSUE'          => ['      P   csTrackerIssue',          null],
        'OPEN_FOR'                  => ['L     P   openFor',                 null],
        'CLOSED_FOR'                => ['L     P   closedFor',               null],
        'COMPLETENESS'              => ['      P   completeness',            null],
        'CONTACT_ALLOWED'           => ['      P F contactAllowed',          null],
        'CONTACT_METHOD'            => ['          contactMethod',           null],
        'CONTACT_ADDRESS_PLAIN'     => ['          contactAddressPlain',     null],
        'CONTACT_INFO_OBFUSCATED'   => ['      P F contactInfoObfuscated',   null],
        'CONTACT_INFO_ORIGINAL'     => ['          contactInfoOriginal',     null],
    ];

    final public const DYNAMIC = [ // Not persisted, but calculated dynamically based on persisted data
        F::SAFE_DOES_NSFW,
        F::SAFE_WORKS_WITH_MINORS,
        F::COMPLETENESS,
    ];

    final public const DATES = [
        F::DATE_ADDED,
        F::DATE_UPDATED,
    ];

    final public const NON_INSPECTED_URLS = [
        F::URL_FURTRACK,
        F::URL_SCRITCH,
        F::URL_PHOTOS,
        F::URL_MINIATURES,
        F::URL_OTHER,
    ];

    final public const IU_FORM_AFFECTED = [
        F::FORMER_MAKER_IDS,
        F::DATE_ADDED,
        F::DATE_UPDATED,
        F::CONTACT_METHOD,
        F::CONTACT_ADDRESS_PLAIN,
        // obfuscated is in form
        F::CONTACT_INFO_ORIGINAL,
    ];

    /**
     * @var array<string,FieldData>
     */
    private static array $fields = [];

    public static function init(): void
    {
        self::$fields = [];

        foreach (Field::cases() as $field) {
            [$data, $validationRegexp] = self::DATA[$field->name];

            self::$fields[$field->name] = new FieldData(
                $field->name,
                substr($data, 10),
                $validationRegexp,
                self::MARK_LIST === $data[0],
                self::MARK_FREE_FORM === $data[2],
                self::MARK_STATS === $data[4],
                self::MARK_PUBLIC === $data[6],
                self::MARK_IN_IU_FORM === $data[8],
                contains(self::DATES, $field),
                !contains(self::DYNAMIC, $field),
            );
        }
    }

    public static function get(Field $field): FieldData
    {
        return self::$fields[$field->name];
    }
}

FieldsData::init();
