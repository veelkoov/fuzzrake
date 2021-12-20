<?php

declare(strict_types=1);

namespace App\DataDefinitions\Fields;

use App\DataDefinitions\Fields\Field as F;
use App\DataDefinitions\Fields\ValidationRegexps as V;
use App\Utils\Traits\UtilityClass;

final class FieldsData
{
    use UtilityClass;

    final public const MARK_LIST = 'L';
    final public const MARK_DYNAMIC = 'D';
    final public const MARK_STATS = 'S';
    final public const MARK_PUBLIC = 'P';
    final public const MARK_FORM = 'F';

    final public const DATA = [
    //
    //                   is in I/U Form ---------F
    //                       is Public? -------P
    //                   show in Stats? -----S
    //                      is Dynamic? ---D
    //  PRETTY_NAME            is List? -L         model name (artisan)      validation regexp
    //
        'MAKER_ID'                  => ['    S P F makerId',                 V::MAKER_ID],
        'FORMER_MAKER_IDS'          => ['L   S P   formerMakerIds',          V::FORMER_MAKER_IDS],
        'NAME'                      => ['    S P F name',                    V::NON_EMPTY],
        'FORMERLY'                  => ['L   S P F formerly',                null],
        'INTRO'                     => ['    S P F intro',                   null],
        'SINCE'                     => ['    S P F since',                   V::SINCE],
        'LANGUAGES'                 => ['L   S P F languages',               null],
        'COUNTRY'                   => ['    S P F country',                 V::COUNTRY],
        'STATE'                     => ['    S P F state',                   V::STATE],
        'CITY'                      => ['    S P F city',                    null],
        'PRODUCTION_MODELS_COMMENT' => ['      P F productionModelsComment', null],
        'PRODUCTION_MODELS'         => ['L   S P F productionModels',        V::LIST_VALIDATION],
        'STYLES_COMMENT'            => ['      P F stylesComment',           null],
        'STYLES'                    => ['L   S P F styles',                  V::LIST_VALIDATION],
        'OTHER_STYLES'              => ['L   S P F otherStyles',             V::LIST_VALIDATION],
        'ORDER_TYPES_COMMENT'       => ['      P F orderTypesComment',       null],
        'ORDER_TYPES'               => ['L   S P F orderTypes',              V::LIST_VALIDATION],
        'OTHER_ORDER_TYPES'         => ['L   S P F otherOrderTypes',         V::LIST_VALIDATION],
        'FEATURES_COMMENT'          => ['      P F featuresComment',         null],
        'FEATURES'                  => ['L   S P F features',                V::LIST_VALIDATION],
        'OTHER_FEATURES'            => ['L   S P F otherFeatures',           V::LIST_VALIDATION],
        'PAYMENT_PLANS'             => ['    S P F paymentPlans',            null],
        'PAYMENT_METHODS'           => ['L   S P F paymentMethods',          V::LIST_VALIDATION],
        'CURRENCIES_ACCEPTED'       => ['L   S P F currenciesAccepted',      V::LIST_VALIDATION],
        'SPECIES_COMMENT'           => ['    S P F speciesComment',          null],
        'SPECIES_DOES'              => ['L   S P F speciesDoes',             ''],
        'SPECIES_DOESNT'            => ['L   S P F speciesDoesnt',           ''],
        'IS_MINOR'                  => ['          isMinor',                 null],
        'AGES'                      => ['    S P F ages',                    null],
        'NSFW_WEBSITE'              => ['        F nsfwWebsite',             null],
        'NSFW_SOCIAL'               => ['        F nsfwSocial',              null],
        'DOES_NSFW'                 => ['        F doesNsfw',                null],
        'SAFE_DOES_NSFW'            => ['  D   P   safeDoesNsfw',            null],
        'WORKS_WITH_MINORS'         => ['        F worksWithMinors',         null],
        'SAFE_WORKS_WITH_MINORS'    => ['  D   P   safeWorksWithMinors',     null],
        'URL_FURSUITREVIEW'         => ['    S P F fursuitReviewUrl',        V::FSR_URL],
        'URL_WEBSITE'               => ['    S P F websiteUrl',              V::GENERIC_URL],
        'URL_PRICES'                => ['L   S P F pricesUrls',              V::GENERIC_URL_LIST],
        'URL_COMMISSIONS'           => ['L   S P F commissionsUrls',         V::GENERIC_URL_LIST],
        'URL_FAQ'                   => ['    S P F faqUrl',                  V::GENERIC_URL],
        'URL_FUR_AFFINITY'          => ['    S P F furAffinityUrl',          V::FA_URL],
        'URL_DEVIANTART'            => ['    S P F deviantArtUrl',           V::DA_URL],
        'URL_TWITTER'               => ['    S P F twitterUrl',              V::TWITTER_URL],
        'URL_FACEBOOK'              => ['    S P F facebookUrl',             V::FACEBOOK_URL],
        'URL_TUMBLR'                => ['    S P F tumblrUrl',               V::TUMBLR_URL],
        'URL_INSTAGRAM'             => ['    S P F instagramUrl',            V::INSTAGRAM_URL],
        'URL_YOUTUBE'               => ['    S P F youtubeUrl',              V::YOUTUBE_URL],
        'URL_LINKLIST'              => ['    S P F linklistUrl',             V::GENERIC_URL],
        'URL_FURRY_AMINO'           => ['    S P F furryAminoUrl',           V::GENERIC_URL],
        'URL_ETSY'                  => ['    S P F etsyUrl',                 V::GENERIC_URL],
        'URL_THE_DEALERS_DEN'       => ['    S P F theDealersDenUrl',        V::GENERIC_URL],
        'URL_OTHER_SHOP'            => ['    S P F otherShopUrl',            V::GENERIC_URL],
        'URL_QUEUE'                 => ['    S P F queueUrl',                V::GENERIC_URL],
        'URL_SCRITCH'               => ['    S P F scritchUrl',              V::SCRITCH_URL],
        'URL_FURTRACK'              => ['    S P F furtrackUrl',             V::FURTRACK_URL],
        'URL_PHOTOS'                => ['L   S P F photoUrls',               V::PHOTO_URL_LIST],
        'URL_MINIATURES'            => ['L   S P   miniatureUrls',           V::MINIATURE_URL_LIST],
        'URL_OTHER'                 => ['    S P F otherUrls',               null],
        'NOTES'                     => ['      P F notes',                   null],
        'INACTIVE_REASON'           => ['    S P   inactiveReason',          null],
        'PASSWORD'                  => ['        F password',                null],
        'CS_LAST_CHECK'             => ['      P   csLastCheck',             null],
        'CS_TRACKER_ISSUE'          => ['      P   csTrackerIssue',          null],
        'BP_LAST_CHECK'             => ['      P   bpLastCheck',             null],
        'BP_TRACKER_ISSUE'          => ['      P   bpTrackerIssue',          null],
        'OPEN_FOR'                  => ['L     P   openFor',                 null],
        'CLOSED_FOR'                => ['L     P   closedFor',               null],
        'COMPLETENESS'              => ['  D   P   completeness',            null],
        'CONTACT_ALLOWED'           => ['      P F contactAllowed',          null],
        'CONTACT_METHOD'            => ['          contactMethod',           null],
        'CONTACT_ADDRESS_PLAIN'     => ['          contactAddressPlain',     null],
        'CONTACT_INFO_OBFUSCATED'   => ['      P F contactInfoObfuscated',   null],
        'CONTACT_INFO_ORIGINAL'     => ['          contactInfoOriginal',     null],
    ];

    final public const NON_INSPECTED_URLS = [
        F::URL_FURTRACK,
        F::URL_SCRITCH,
        F::URL_PHOTOS,
        F::URL_MINIATURES,
        F::URL_OTHER,
    ];

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
                self::MARK_DYNAMIC !== $data[2],
                self::MARK_STATS === $data[4],
                self::MARK_PUBLIC === $data[6],
                self::MARK_FORM === $data[8],
            );
        }
    }

    public static function get(Field $field): FieldData
    {
        return self::$fields[$field->name];
    }
}

FieldsData::init();
