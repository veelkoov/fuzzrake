<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\Utils\Artisan\ValidationRegexps as V;
use App\Utils\Artisan\Fields as F;
use App\Utils\Traits\UtilityClass;

final class FieldsDefinitions
{
    use UtilityClass;

    public const FIELDS_ARRAY_DATA = [
    //
    //                         IS PUBLIC? ----------.
    //                     SHOW IN STATS? -------.  |
    //                      IS PERSISTED? ----.  |  |
    //                           IS LIST? -.  |  |  |
    //  PRETTY_NAME                        V  V  V  V  model name (artisan)        validation regexp
    //
        F::MAKER_ID                   => [ 0, 1, 1, 1, 'makerId',                  V::MAKER_ID ],
        F::FORMER_MAKER_IDS           => [ 1, 1, 1, 1, 'formerMakerIds',           V::FORMER_MAKER_IDS ],
        F::NAME                       => [ 0, 1, 1, 1, 'name',                     V::NON_EMPTY ],
        F::FORMERLY                   => [ 1, 1, 1, 1, 'formerly',                 V::ANYTHING ],
        F::INTRO                      => [ 0, 1, 1, 1, 'intro',                    V::ANYTHING ],
        F::SINCE                      => [ 0, 1, 1, 1, 'since',                    V::SINCE ],
        F::LANGUAGES                  => [ 1, 1, 1, 1, 'languages',                V::ANYTHING ],
        F::COUNTRY                    => [ 0, 1, 1, 1, 'country',                  V::COUNTRY ],
        F::STATE                      => [ 0, 1, 1, 1, 'state',                    V::ANYTHING ],
        F::CITY                       => [ 0, 1, 1, 1, 'city',                     V::ANYTHING ],
        F::PRODUCTION_MODELS_COMMENT  => [ 0, 1, 0, 1, 'productionModelsComment',  V::ANYTHING ],
        F::PRODUCTION_MODELS          => [ 1, 1, 1, 1, 'productionModels',         V::LIST_VALIDATION ],
        F::STYLES_COMMENT             => [ 0, 1, 0, 1, 'stylesComment',            V::ANYTHING ],
        F::STYLES                     => [ 1, 1, 1, 1, 'styles',                   V::LIST_VALIDATION ],
        F::OTHER_STYLES               => [ 1, 1, 1, 1, 'otherStyles',              V::LIST_VALIDATION ],
        F::ORDER_TYPES_COMMENT        => [ 0, 1, 0, 1, 'orderTypesComment',        V::ANYTHING ],
        F::ORDER_TYPES                => [ 1, 1, 1, 1, 'orderTypes',               V::LIST_VALIDATION ],
        F::OTHER_ORDER_TYPES          => [ 1, 1, 1, 1, 'otherOrderTypes',          V::LIST_VALIDATION ],
        F::FEATURES_COMMENT           => [ 0, 1, 0, 1, 'featuresComment',          V::ANYTHING ],
        F::FEATURES                   => [ 1, 1, 1, 1, 'features',                 V::LIST_VALIDATION ],
        F::OTHER_FEATURES             => [ 1, 1, 1, 1, 'otherFeatures',            V::LIST_VALIDATION ],
        F::PAYMENT_PLANS              => [ 0, 1, 1, 1, 'paymentPlans',             V::ANYTHING ],
        F::PAYMENT_METHODS            => [ 1, 1, 1, 1, 'paymentMethods',           V::LIST_VALIDATION ],
        F::CURRENCIES_ACCEPTED        => [ 1, 1, 1, 1, 'currenciesAccepted',       V::LIST_VALIDATION ],
        F::SPECIES_COMMENT            => [ 0, 1, 1, 1, 'speciesComment',           V::ANYTHING ],
        F::SPECIES_DOES               => [ 1, 1, 1, 1, 'speciesDoes',              V::ANYTHING ],
        F::SPECIES_DOESNT             => [ 1, 1, 1, 1, 'speciesDoesnt',            V::ANYTHING ],
        F::URL_FURSUITREVIEW          => [ 0, 1, 1, 1, 'fursuitReviewUrl',         V::FSR_URL ],
        F::URL_WEBSITE                => [ 0, 1, 1, 1, 'websiteUrl',               V::GENERIC_URL ],
        F::URL_PRICES                 => [ 1, 1, 1, 1, 'pricesUrl',                V::GENERIC_URL_LIST ],
        F::URL_COMMISSIONS            => [ 1, 1, 1, 1, 'commissionsUrl',           V::GENERIC_URL_LIST ],
        F::URL_FAQ                    => [ 0, 1, 1, 1, 'faqUrl',                   V::GENERIC_URL ],
        F::URL_FUR_AFFINITY           => [ 0, 1, 1, 1, 'furAffinityUrl',           V::FA_URL ],
        F::URL_DEVIANTART             => [ 0, 1, 1, 1, 'deviantArtUrl',            V::DA_URL ],
        F::URL_TWITTER                => [ 0, 1, 1, 1, 'twitterUrl',               V::TWITTER_URL ],
        F::URL_FACEBOOK               => [ 0, 1, 1, 1, 'facebookUrl',              V::FACEBOOK_URL ],
        F::URL_TUMBLR                 => [ 0, 1, 1, 1, 'tumblrUrl',                V::TUMBLR_URL ],
        F::URL_INSTAGRAM              => [ 0, 1, 1, 1, 'instagramUrl',             V::INSTAGRAM_URL ],
        F::URL_YOUTUBE                => [ 0, 1, 1, 1, 'youtubeUrl',               V::YOUTUBE_URL ],
        F::URL_LINKLIST               => [ 0, 1, 1, 1, 'linklistUrl',              V::GENERIC_URL ],
        F::URL_FURRY_AMINO            => [ 0, 1, 1, 1, 'furryAminoUrl',            V::GENERIC_URL ],
        F::URL_ETSY                   => [ 0, 1, 1, 1, 'etsyUrl',                  V::GENERIC_URL ],
        F::URL_THE_DEALERS_DEN        => [ 0, 1, 1, 1, 'theDealersDenUrl',         V::GENERIC_URL ],
        F::URL_OTHER_SHOP             => [ 0, 1, 1, 1, 'otherShopUrl',             V::GENERIC_URL ],
        F::URL_QUEUE                  => [ 0, 1, 1, 1, 'queueUrl',                 V::GENERIC_URL ],
        F::URL_SCRITCH                => [ 0, 1, 1, 1, 'scritchUrl',               V::SCRITCH_URL ],
        F::URL_FURTRACK               => [ 0, 1, 1, 1, 'furtrackUrl',              V::FURTRACK_URL ],
        F::URL_PHOTOS                 => [ 1, 1, 1, 1, 'photoUrls',                V::PHOTO_URL_LIST ],
        F::URL_MINIATURES             => [ 1, 1, 1, 1, 'miniatureUrls',            V::MINIATURE_URL_LIST ],
        F::URL_OTHER                  => [ 0, 1, 1, 1, 'otherUrls',                V::ANYTHING ],
        F::NOTES                      => [ 0, 1, 0, 1, 'notes',                    V::ANYTHING ],
        F::INACTIVE_REASON            => [ 0, 1, 1, 1, 'inactiveReason',           V::ANYTHING ],
        F::CS_LAST_CHECK              => [ 0, 0, 0, 1, 'csLastCheck',              V::ANYTHING ],
        F::CS_TRACKER_ISSUE           => [ 0, 1, 0, 1, 'csTrackerIssue',           V::ANYTHING ],
        F::BP_LAST_CHECK              => [ 0, 0, 0, 1, 'bpLastCheck',              V::ANYTHING ],
        F::BP_TRACKER_ISSUE           => [ 0, 1, 0, 1, 'bpTrackerIssue',           V::ANYTHING ],
        F::OPEN_FOR                   => [ 1, 0, 0, 1, 'openFor',                  V::ANYTHING ],
        F::CLOSED_FOR                 => [ 1, 0, 0, 1, 'closedFor',                V::ANYTHING ],
        F::COMPLETENESS               => [ 0, 0, 0, 1, 'completeness',             V::ANYTHING ],
        F::CONTACT_ALLOWED            => [ 0, 1, 0, 1, 'contactAllowed',           V::ANYTHING ],
        F::CONTACT_METHOD             => [ 0, 1, 0, 0, 'contactMethod',            V::ANYTHING ],
        F::CONTACT_ADDRESS_PLAIN      => [ 0, 1, 0, 0, 'contactAddressPlain',      V::ANYTHING ],
        F::CONTACT_INFO_ORIGINAL      => [ 0, 1, 0, 0, 'contactInfoOriginal',      V::ANYTHING ],
        F::CONTACT_INFO_OBFUSCATED    => [ 0, 1, 0, 1, 'contactInfoObfuscated',    V::ANYTHING ],
        F::PASSWORD                   => [ 0, 1, 0, 0, 'password',                 V::ANYTHING ],
    ];

    public const URLS = [
        F::URL_FURSUITREVIEW,
        F::URL_WEBSITE,
        F::URL_PRICES,
        F::URL_FAQ,
        F::URL_FUR_AFFINITY,
        F::URL_DEVIANTART,
        F::URL_TWITTER,
        F::URL_FACEBOOK,
        F::URL_TUMBLR,
        F::URL_INSTAGRAM,
        F::URL_YOUTUBE,
        F::URL_LINKLIST,
        F::URL_FURRY_AMINO,
        F::URL_ETSY,
        F::URL_THE_DEALERS_DEN,
        F::URL_OTHER_SHOP,
        F::URL_QUEUE,
        F::URL_SCRITCH,
        F::URL_PHOTOS,
        F::URL_MINIATURES,
        F::URL_OTHER,
        F::URL_COMMISSIONS,
    ];

    public const NON_INSPECTED_URLS = [
        F::URL_SCRITCH,
        F::URL_PHOTOS,
        F::URL_MINIATURES,
        F::URL_OTHER,
    ];
}
