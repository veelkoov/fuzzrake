<?php

declare(strict_types=1);

namespace App\Tests\DataDefinitions\Fields;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\ValidationRegexps as V;
use PHPUnit\Framework\TestCase;

class FieldsDataTest extends TestCase
{
    public const FIELDS_ARRAY_DATA = [
        //
        //                   IS IN I/U FORM -------------.
        //                       IS PUBLIC? ----------.  |
        //                   SHOW IN STATS? -------.  |  |
        //                    IS PERSISTED? ----.  |  |  |
        //                         IS LIST? -.  |  |  |  |
        //  PRETTY_NAME                      V  V  V  V  V  model name (artisan)        validation regexp
        //
        'MAKER_ID'                   => [0, 1, 1, 1, 1, 'makerId',                  V::MAKER_ID],
        'FORMER_MAKER_IDS'           => [1, 1, 1, 1, 0, 'formerMakerIds',           V::FORMER_MAKER_IDS],
        'NAME'                       => [0, 1, 1, 1, 1, 'name',                     V::NON_EMPTY],
        'FORMERLY'                   => [1, 1, 1, 1, 1, 'formerly',                 null],
        'INTRO'                      => [0, 1, 1, 1, 1, 'intro',                    null],
        'SINCE'                      => [0, 1, 1, 1, 1, 'since',                    V::SINCE],
        'LANGUAGES'                  => [1, 1, 1, 1, 1, 'languages',                null],
        'COUNTRY'                    => [0, 1, 1, 1, 1, 'country',                  V::COUNTRY],
        'STATE'                      => [0, 1, 1, 1, 1, 'state',                    V::STATE],
        'CITY'                       => [0, 1, 1, 1, 1, 'city',                     null],
        'PRODUCTION_MODELS_COMMENT'  => [0, 1, 0, 1, 1, 'productionModelsComment',  null],
        'PRODUCTION_MODELS'          => [1, 1, 1, 1, 1, 'productionModels',         V::LIST_VALIDATION],
        'STYLES_COMMENT'             => [0, 1, 0, 1, 1, 'stylesComment',            null],
        'STYLES'                     => [1, 1, 1, 1, 1, 'styles',                   V::LIST_VALIDATION],
        'OTHER_STYLES'               => [1, 1, 1, 1, 1, 'otherStyles',              V::LIST_VALIDATION],
        'ORDER_TYPES_COMMENT'        => [0, 1, 0, 1, 1, 'orderTypesComment',        null],
        'ORDER_TYPES'                => [1, 1, 1, 1, 1, 'orderTypes',               V::LIST_VALIDATION],
        'OTHER_ORDER_TYPES'          => [1, 1, 1, 1, 1, 'otherOrderTypes',          V::LIST_VALIDATION],
        'FEATURES_COMMENT'           => [0, 1, 0, 1, 1, 'featuresComment',          null],
        'FEATURES'                   => [1, 1, 1, 1, 1, 'features',                 V::LIST_VALIDATION],
        'OTHER_FEATURES'             => [1, 1, 1, 1, 1, 'otherFeatures',            V::LIST_VALIDATION],
        'PAYMENT_PLANS'              => [0, 1, 1, 1, 1, 'paymentPlans',             null],
        'PAYMENT_METHODS'            => [1, 1, 1, 1, 1, 'paymentMethods',           V::LIST_VALIDATION],
        'CURRENCIES_ACCEPTED'        => [1, 1, 1, 1, 1, 'currenciesAccepted',       V::LIST_VALIDATION],
        'SPECIES_COMMENT'            => [0, 1, 1, 1, 1, 'speciesComment',           null],
        'SPECIES_DOES'               => [1, 1, 1, 1, 1, 'speciesDoes',              ''],
        'SPECIES_DOESNT'             => [1, 1, 1, 1, 1, 'speciesDoesnt',            ''],
        'IS_MINOR'                   => [0, 1, 1, 1, 1, 'isMinor',                  null],
        'WORKS_WITH_MINORS'          => [0, 1, 1, 1, 1, 'worksWithMinors',          null],
        'URL_FURSUITREVIEW'          => [0, 1, 1, 1, 1, 'fursuitReviewUrl',         V::FSR_URL],
        'URL_WEBSITE'                => [0, 1, 1, 1, 1, 'websiteUrl',               V::GENERIC_URL],
        'URL_PRICES'                 => [1, 1, 1, 1, 1, 'pricesUrls',               V::GENERIC_URL_LIST],
        'URL_COMMISSIONS'            => [1, 1, 1, 1, 1, 'commissionsUrls',          V::GENERIC_URL_LIST],
        'URL_FAQ'                    => [0, 1, 1, 1, 1, 'faqUrl',                   V::GENERIC_URL],
        'URL_FUR_AFFINITY'           => [0, 1, 1, 1, 1, 'furAffinityUrl',           V::FA_URL],
        'URL_DEVIANTART'             => [0, 1, 1, 1, 1, 'deviantArtUrl',            V::DA_URL],
        'URL_TWITTER'                => [0, 1, 1, 1, 1, 'twitterUrl',               V::TWITTER_URL],
        'URL_FACEBOOK'               => [0, 1, 1, 1, 1, 'facebookUrl',              V::FACEBOOK_URL],
        'URL_TUMBLR'                 => [0, 1, 1, 1, 1, 'tumblrUrl',                V::TUMBLR_URL],
        'URL_INSTAGRAM'              => [0, 1, 1, 1, 1, 'instagramUrl',             V::INSTAGRAM_URL],
        'URL_YOUTUBE'                => [0, 1, 1, 1, 1, 'youtubeUrl',               V::YOUTUBE_URL],
        'URL_LINKLIST'               => [0, 1, 1, 1, 1, 'linklistUrl',              V::GENERIC_URL],
        'URL_FURRY_AMINO'            => [0, 1, 1, 1, 1, 'furryAminoUrl',            V::GENERIC_URL],
        'URL_ETSY'                   => [0, 1, 1, 1, 1, 'etsyUrl',                  V::GENERIC_URL],
        'URL_THE_DEALERS_DEN'        => [0, 1, 1, 1, 1, 'theDealersDenUrl',         V::GENERIC_URL],
        'URL_OTHER_SHOP'             => [0, 1, 1, 1, 1, 'otherShopUrl',             V::GENERIC_URL],
        'URL_QUEUE'                  => [0, 1, 1, 1, 1, 'queueUrl',                 V::GENERIC_URL],
        'URL_SCRITCH'                => [0, 1, 1, 1, 1, 'scritchUrl',               V::SCRITCH_URL],
        'URL_FURTRACK'               => [0, 1, 1, 1, 1, 'furtrackUrl',              V::FURTRACK_URL],
        'URL_PHOTOS'                 => [1, 1, 1, 1, 1, 'photoUrls',                V::PHOTO_URL_LIST],
        'URL_MINIATURES'             => [1, 1, 1, 1, 0, 'miniatureUrls',            V::MINIATURE_URL_LIST],
        'URL_OTHER'                  => [0, 1, 1, 1, 1, 'otherUrls',                null],
        'NOTES'                      => [0, 1, 0, 1, 1, 'notes',                    null],
        'INACTIVE_REASON'            => [0, 1, 1, 1, 0, 'inactiveReason',           null],
        'PASSWORD'                   => [0, 1, 0, 0, 1, 'password',                 null],
        'CS_LAST_CHECK'              => [0, 1, 0, 1, 0, 'csLastCheck',              null],
        'CS_TRACKER_ISSUE'           => [0, 1, 0, 1, 0, 'csTrackerIssue',           null],
        'BP_LAST_CHECK'              => [0, 1, 0, 1, 0, 'bpLastCheck',              null],
        'BP_TRACKER_ISSUE'           => [0, 1, 0, 1, 0, 'bpTrackerIssue',           null],
        'OPEN_FOR'                   => [1, 1, 0, 1, 0, 'openFor',                  null],
        'CLOSED_FOR'                 => [1, 1, 0, 1, 0, 'closedFor',                null],
        'COMPLETENESS'               => [0, 0, 0, 1, 0, 'completeness',             null],
        'CONTACT_ALLOWED'            => [0, 1, 0, 1, 1, 'contactAllowed',           null],
        'CONTACT_METHOD'             => [0, 1, 0, 0, 0, 'contactMethod',            null],
        'CONTACT_ADDRESS_PLAIN'      => [0, 1, 0, 0, 0, 'contactAddressPlain',      null],
        'CONTACT_INFO_OBFUSCATED'    => [0, 1, 0, 1, 1, 'contactInfoObfuscated',    null],
        'CONTACT_INFO_ORIGINAL'      => [0, 1, 0, 0, 0, 'contactInfoOriginal',      null],
    ];

    public function testSanityChecks(): void
    {
        foreach (Field::cases() as $field) {
            self::assertEquals($field->isList(), (bool) self::FIELDS_ARRAY_DATA[$field->name][0]);
            self::assertEquals($field->isPersisted(), (bool) self::FIELDS_ARRAY_DATA[$field->name][1]);
            self::assertEquals($field->inStats(), (bool) self::FIELDS_ARRAY_DATA[$field->name][2]);
            self::assertEquals($field->public(), (bool) self::FIELDS_ARRAY_DATA[$field->name][3]);
            self::assertEquals($field->isInIuForm(), (bool) self::FIELDS_ARRAY_DATA[$field->name][4]);
            self::assertEquals($field->modelName(), self::FIELDS_ARRAY_DATA[$field->name][5]);
            self::assertEquals($field->getData()->validationRegexp, self::FIELDS_ARRAY_DATA[$field->name][6]);
        }
    }
}
