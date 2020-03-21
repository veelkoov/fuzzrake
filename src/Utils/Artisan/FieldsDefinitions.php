<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\Utils\Artisan\ValidationRegexps as VR;

final class FieldsDefinitions
{
    public const FIELDS_ARRAY_DATA = [
        /*                                                                             EXPORTED IN JSON? ----------.
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
        Fields::URL_PRICES                 => ['pricesUrl',                VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_FAQ                    => ['faqUrl',                   VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_FUR_AFFINITY           => ['furAffinityUrl',           VR::FA_URL,                    0, 1, 1, 1],
        Fields::URL_DEVIANTART             => ['deviantArtUrl',            VR::DA_URL,                    0, 1, 1, 1],
        Fields::URL_TWITTER                => ['twitterUrl',               VR::TWITTER_URL,               0, 1, 1, 1],
        Fields::URL_FACEBOOK               => ['facebookUrl',              VR::FACEBOOK_URL,              0, 1, 1, 1],
        Fields::URL_TUMBLR                 => ['tumblrUrl',                VR::TUMBLR_URL,                0, 1, 1, 1],
        Fields::URL_INSTAGRAM              => ['instagramUrl',             VR::INSTAGRAM_URL,             0, 1, 1, 1],
        Fields::URL_YOUTUBE                => ['youtubeUrl',               VR::YOUTUBE_URL,               0, 1, 1, 1],
        Fields::URL_LINKTREE               => ['linktreeUrl',              VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_FURRY_AMINO            => ['furryAminoUrl',            VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_ETSY                   => ['etsyUrl',                  VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_THE_DEALERS_DEN        => ['theDealersDenUrl',         VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_OTHER_SHOP             => ['otherShopUrl',             VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_QUEUE                  => ['queueUrl',                 VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::URL_SCRITCH                => ['scritchUrl',               VR::SCRITCH_URL,               0, 1, 1, 1],
        Fields::URL_SCRITCH_PHOTO          => ['scritchPhotoUrls',         VR::SCRITCH_PHOTO_URLS,        1, 1, 1, 1],
        Fields::URL_SCRITCH_MINIATURE      => ['scritchMiniatureUrls',     VR::SCRITCH_MINIATURE_URLS,    1, 1, 1, 1],
        Fields::URL_OTHER                  => ['otherUrls',                VR::ANYTHING,                  0, 1, 1, 1],

        Fields::NOTES                      => ['notes',                    VR::ANYTHING,                  0, 1, 0, 1],
        Fields::INACTIVE_REASON            => ['inactiveReason',           VR::ANYTHING,                  0, 1, 1, 1],
        Fields::URL_CST                    => ['cstUrl',                   VR::GENERIC_URL,               0, 1, 1, 1],
        Fields::COMMISSIONS_STATUS         => ['commissionsStatus',        null,                          0, 0, 0, 1],
        Fields::CST_LAST_CHECK             => ['cstLastCheck',             null,                          0, 0, 0, 1],
        Fields::COMPLETENESS               => ['completeness',             null,                          0, 0, 0, 1],

        Fields::CONTACT_ALLOWED            => ['contactAllowed',           null,                          0, 1, 0, 1],
        Fields::CONTACT_METHOD             => ['contactMethod',            null,                          0, 1, 0, 0],
        Fields::CONTACT_ADDRESS_PLAIN      => ['contactAddressPlain',      null,                          0, 1, 0, 0],
        Fields::CONTACT_INFO_ORIGINAL      => ['contactInfoOriginal',      null,                          0, 1, 0, 0],
        Fields::CONTACT_INFO_OBFUSCATED    => ['contactInfoObfuscated',    null,                          0, 1, 0, 1],

        Fields::PASSCODE                   => ['passcode',                 null,                          0, 1, 0, 0],
        Fields::TIMESTAMP                  => [null,                       null,                          0, 0, 0, 0],
        Fields::VALIDATION_CHECKBOX        => [null,                       null,                          0, 0, 0, 0],
        Fields::CONTACT_INPUT_VIRTUAL      => [null,                       null,                          0, 0, 0, 0],
    ];

    public const IU_FORM_FIELDS_ORDERED = [
        /*                                                  EXPORT TO I/U FORM ----.
         *                                                IMPORT FROM I/U FORM -.  |
         * PRETTY_NAME                => ['regexp 4 name in form'               V  V
         */
        Fields::TIMESTAMP             => [null,                                 0, 0],
        Fields::VALIDATION_CHECKBOX   => ['#update#',                           0, 1],
        Fields::NAME                  => ['#studio/maker\'s name#i',            1, 1],
        Fields::FORMERLY              => ['#formerly#i',                        1, 1],
        Fields::SINCE                 => ['#since when#i',                      1, 1],
        Fields::COUNTRY               => ['#country#i',                         1, 1],
        Fields::STATE                 => ['#what state is it in#i',             1, 1],
        Fields::CITY                  => ['#city#i',                            1, 1],
        Fields::PAYMENT_PLANS         => ['#payment plans#i',                   1, 1],
        Fields::URL_PRICES            => ['#prices list#i',                     1, 1],
        Fields::PRODUCTION_MODELS     => ['#What do you do#i',                  1, 1],
        Fields::STYLES                => ['#What styles#i',                     1, 1],
        Fields::OTHER_STYLES          => ['#Any other styles#i',                1, 1],
        Fields::ORDER_TYPES           => ['#What kind of#i',                    1, 1],
        Fields::OTHER_ORDER_TYPES     => ['#Any other kinds/items#i',           1, 1],
        Fields::FEATURES              => ['#What features#i',                   1, 1],
        Fields::OTHER_FEATURES        => ['#Any other features#i',              1, 1],
        Fields::SPECIES_DOES          => ['#What species#i',                    1, 1],
        Fields::SPECIES_DOESNT        => ['#species you will NOT#i',            1, 1],
        Fields::URL_FURSUITREVIEW     => ['#fursuitreview#i',                   1, 1],
        Fields::URL_WEBSITE           => ['#regular website#i',                 1, 1],
        Fields::URL_FAQ               => ['#FAQ#i',                             1, 1],
        Fields::URL_QUEUE             => ['#queue/progress#i',                  1, 1],
        Fields::URL_FUR_AFFINITY      => ['#FurAffinity#i',                     1, 1],
        Fields::URL_DEVIANTART        => ['#DeviantArt#i',                      1, 1],
        Fields::URL_TWITTER           => ['#Twitter#i',                         1, 1],
        Fields::URL_FACEBOOK          => ['#Facebook#i',                        1, 1],
        Fields::URL_TUMBLR            => ['#Tumblr#i',                          1, 1],
        Fields::URL_INSTAGRAM         => ['#Instagram#i',                       1, 1],
        Fields::URL_YOUTUBE           => ['#YouTube#i',                         1, 1],
        Fields::URL_OTHER             => ['#other websites#i',                  1, 1],
        Fields::URL_CST               => ['#commissions status#i',              1, 1],
        Fields::URL_SCRITCH           => ['#Got scritch\.es\?#i',               1, 1],
        Fields::URL_SCRITCH_PHOTO     => ['#"featured" photos#i',               1, 1],
        Fields::LANGUAGES             => ['#languages#i',                       1, 1],
        Fields::MAKER_ID              => ['#Maker ID#i',                        1, 1],
        Fields::INTRO                 => ['#intro#i',                           1, 1],
        Fields::NOTES                 => ['#notes#i',                           1, 1],
        Fields::PASSCODE              => ['#passcode#i',                        1, 0],
        Fields::CONTACT_ALLOWED       => ['#Permit to contact#i',               1, 1],
        Fields::CONTACT_INPUT_VIRTUAL => ['#How can I contact#i',               1, 1],
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
        Fields::URL_LINKTREE,
        Fields::URL_FURRY_AMINO,
        Fields::URL_ETSY,
        Fields::URL_THE_DEALERS_DEN,
        Fields::URL_OTHER_SHOP,
        Fields::URL_QUEUE,
        Fields::URL_SCRITCH,
        Fields::URL_SCRITCH_PHOTO,
        Fields::URL_SCRITCH_MINIATURE,
        Fields::URL_OTHER,
        Fields::URL_CST,
    ];

    public const NON_INSPECTED_URLS = [
        Fields::URL_SCRITCH,
        Fields::URL_SCRITCH_PHOTO,
        Fields::URL_SCRITCH_MINIATURE,
        Fields::URL_OTHER,
    ];

    private function __construct()
    {
    }
}
