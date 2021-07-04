<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\Utils\Artisan\ValidationRegexps as VR;
use App\Utils\Traits\UtilityClass;

final class FieldsDefinitions
{
    use UtilityClass;

    public const FIELDS_ARRAY_DATA = [
        /*                                                                                    IS PUBLIC? ----------.
         *                                                                                SHOW IN STATS? -------.  |
         *                                                                                 IS PERSISTED? ----.  |  |
         *                                                                                      IS LIST? -.  |  |  |
         * PRETTY_NAME                     => ['model name (artisan)',     'validation regexp',           V  V  V  V
         */
        Fields::MAKER_ID                   => ['makerId',                  VR::MAKER_ID,                  0, 1, 1, 1],
        Fields::FORMER_MAKER_IDS           => ['formerMakerIds',           VR::FORMER_MAKER_IDS,          1, 1, 1, 1],

        Fields::NAME                       => ['name',                     VR::NON_EMPTY,                 0, 1, 1, 1],
        Fields::FORMERLY                   => ['formerly',                 VR::ANYTHING,                  1, 1, 1, 1],

        Fields::INTRO                      => ['intro',                    VR::ANYTHING,                  0, 1, 1, 1],
        Fields::SINCE                      => ['since',                    VR::SINCE,                     0, 1, 1, 1],

        Fields::LANGUAGES                  => ['languages',                VR::ANYTHING,                  1, 1, 1, 1],
        Fields::COUNTRY                    => ['country',                  VR::COUNTRY,                   0, 1, 1, 1],
        Fields::STATE                      => ['state',                    VR::ANYTHING,                  0, 1, 1, 1],
        Fields::CITY                       => ['city',                     VR::ANYTHING,                  0, 1, 1, 1],

        Fields::PRODUCTION_MODELS_COMMENT  => ['productionModelsComment',  VR::ANYTHING,                  0, 1, 0, 1],
        Fields::PRODUCTION_MODELS          => ['productionModels',         VR::LIST_VALIDATION,           1, 1, 1, 1],

        Fields::STYLES_COMMENT             => ['stylesComment',            VR::ANYTHING,                  0, 1, 0, 1],
        Fields::STYLES                     => ['styles',                   VR::LIST_VALIDATION,           1, 1, 1, 1],
        Fields::OTHER_STYLES               => ['otherStyles',              VR::LIST_VALIDATION,           1, 1, 1, 1],

        Fields::ORDER_TYPES_COMMENT        => ['orderTypesComment',        VR::ANYTHING,                  0, 1, 0, 1],
        Fields::ORDER_TYPES                => ['orderTypes',               VR::LIST_VALIDATION,           1, 1, 1, 1],
        Fields::OTHER_ORDER_TYPES          => ['otherOrderTypes',          VR::LIST_VALIDATION,           1, 1, 1, 1],

        Fields::FEATURES_COMMENT           => ['featuresComment',          VR::ANYTHING,                  0, 1, 0, 1],
        Fields::FEATURES                   => ['features',                 VR::LIST_VALIDATION,           1, 1, 1, 1],
        Fields::OTHER_FEATURES             => ['otherFeatures',            VR::LIST_VALIDATION,           1, 1, 1, 1],

        Fields::PAYMENT_PLANS              => ['paymentPlans',             VR::ANYTHING,                  0, 1, 1, 1],
        Fields::PAYMENT_METHODS            => ['paymentMethods',           VR::LIST_VALIDATION,           1, 1, 1, 1],
        Fields::CURRENCIES_ACCEPTED        => ['currenciesAccepted',       VR::LIST_VALIDATION,           1, 1, 1, 1],

        Fields::SPECIES_COMMENT            => ['speciesComment',           VR::ANYTHING,                  0, 1, 1, 1],
        Fields::SPECIES_DOES               => ['speciesDoes',              VR::ANYTHING,                  1, 1, 1, 1],
        Fields::SPECIES_DOESNT             => ['speciesDoesnt',            VR::ANYTHING,                  1, 1, 1, 1],

        Fields::URL_FURSUITREVIEW          => ['fursuitReviewUrl',         VR::FSR_URL,                   0, 1, 1, 1],
        Fields::URL_WEBSITE                => ['websiteUrl',               VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_PRICES                 => ['pricesUrl',                VR::GENERIC_URL_LIST,          1, 1, 1, 1],
        Fields::URL_COMMISSIONS            => ['commissionsUrl',           VR::GENERIC_URL_LIST,          1, 1, 1, 1],
        Fields::URL_FAQ                    => ['faqUrl',                   VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_FUR_AFFINITY           => ['furAffinityUrl',           VR::FA_URL,                    0, 1, 1, 1],
        Fields::URL_DEVIANTART             => ['deviantArtUrl',            VR::DA_URL,                    0, 1, 1, 1],
        Fields::URL_TWITTER                => ['twitterUrl',               VR::TWITTER_URL,               0, 1, 1, 1],
        Fields::URL_FACEBOOK               => ['facebookUrl',              VR::FACEBOOK_URL,              0, 1, 1, 1],
        Fields::URL_TUMBLR                 => ['tumblrUrl',                VR::TUMBLR_URL,                0, 1, 1, 1],
        Fields::URL_INSTAGRAM              => ['instagramUrl',             VR::INSTAGRAM_URL,             0, 1, 1, 1],
        Fields::URL_YOUTUBE                => ['youtubeUrl',               VR::YOUTUBE_URL,               0, 1, 1, 1],
        Fields::URL_LINKLIST               => ['linklistUrl',              VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_FURRY_AMINO            => ['furryAminoUrl',            VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_ETSY                   => ['etsyUrl',                  VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_THE_DEALERS_DEN        => ['theDealersDenUrl',         VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_OTHER_SHOP             => ['otherShopUrl',             VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_QUEUE                  => ['queueUrl',                 VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_SCRITCH                => ['scritchUrl',               VR::SCRITCH_URL,               0, 1, 1, 1],
        Fields::URL_FURTRACK               => ['furtrackUrl',              VR::FURTRACK_URL,              0, 1, 1, 1],
        Fields::URL_PHOTOS                 => ['photoUrls',                VR::PHOTO_URL_LIST,            1, 1, 1, 1],
        Fields::URL_MINIATURES             => ['miniatureUrls',            VR::MINIATURE_URL_LIST,        1, 1, 1, 1],
        Fields::URL_OTHER                  => ['otherUrls',                VR::ANYTHING,                  0, 1, 1, 1],

        Fields::NOTES                      => ['notes',                    VR::ANYTHING,                  0, 1, 0, 1],
        Fields::INACTIVE_REASON            => ['inactiveReason',           VR::ANYTHING,                  0, 1, 1, 1],
        Fields::CS_LAST_CHECK              => ['csLastCheck',              VR::ANYTHING,                  0, 0, 0, 1],
        Fields::CS_TRACKER_ISSUE           => ['csTrackerIssue',           VR::ANYTHING,                  0, 0, 0, 1],
        Fields::BP_LAST_CHECK              => ['bpLastCheck',              VR::ANYTHING,                  0, 0, 0, 1],
        Fields::BP_TRACKER_ISSUE           => ['bpTrackerIssue',           VR::ANYTHING,                  0, 0, 0, 1],
        Fields::OPEN_FOR                   => ['openFor',                  VR::ANYTHING,                  1, 0, 0, 1],
        Fields::CLOSED_FOR                 => ['closedFor',                VR::ANYTHING,                  1, 0, 0, 1],
        Fields::COMPLETENESS               => ['completeness',             VR::ANYTHING,                  0, 0, 0, 1],

        Fields::CONTACT_ALLOWED            => ['contactAllowed',           VR::ANYTHING,                  0, 1, 0, 1],
        Fields::CONTACT_METHOD             => ['contactMethod',            VR::ANYTHING,                  0, 1, 0, 0],
        Fields::CONTACT_ADDRESS_PLAIN      => ['contactAddressPlain',      VR::ANYTHING,                  0, 1, 0, 0],
        Fields::CONTACT_INFO_ORIGINAL      => ['contactInfoOriginal',      VR::ANYTHING,                  0, 1, 0, 0],
        Fields::CONTACT_INFO_OBFUSCATED    => ['contactInfoObfuscated',    VR::ANYTHING,                  0, 1, 0, 1],

        Fields::PASSWORD                   => ['password',                 VR::ANYTHING,                  0, 1, 0, 0],
    ];

    public const URLS = [
        Fields::URL_FURSUITREVIEW,
        Fields::URL_WEBSITE,
        Fields::URL_PRICES,
        Fields::URL_FAQ,
        Fields::URL_FUR_AFFINITY,
        Fields::URL_DEVIANTART,
        Fields::URL_TWITTER,
        Fields::URL_FACEBOOK,
        Fields::URL_TUMBLR,
        Fields::URL_INSTAGRAM,
        Fields::URL_YOUTUBE,
        Fields::URL_LINKLIST,
        Fields::URL_FURRY_AMINO,
        Fields::URL_ETSY,
        Fields::URL_THE_DEALERS_DEN,
        Fields::URL_OTHER_SHOP,
        Fields::URL_QUEUE,
        Fields::URL_SCRITCH,
        Fields::URL_PHOTOS,
        Fields::URL_MINIATURES,
        Fields::URL_OTHER,
        Fields::URL_COMMISSIONS,
    ];

    public const NON_INSPECTED_URLS = [
        Fields::URL_SCRITCH,
        Fields::URL_PHOTOS,
        Fields::URL_MINIATURES,
        Fields::URL_OTHER,
    ];
}
