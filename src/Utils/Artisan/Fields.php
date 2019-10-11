<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use InvalidArgumentException;
use App\Utils\Artisan\ValidationRegexps as VR;

class Fields
{
    /***** "PRETTY" NAMES START *****/
    public const TIMESTAMP = 'TIMESTAMP';
    public const VALIDATION_CHECKBOX = 'VALIDATION_CHECKBOX';
    public const NAME = 'NAME';
    public const FORMERLY = 'FORMERLY';
    public const SINCE = 'SINCE';
    public const COUNTRY = 'COUNTRY';
    public const STATE = 'STATE';
    public const CITY = 'CITY';
    public const PAYMENT_PLANS = 'PAYMENT_PLANS';
    public const PRODUCTION_MODELS = 'PRODUCTION_MODELS';
    public const STYLES = 'STYLES';
    public const OTHER_STYLES = 'OTHER_STYLES';
    public const ORDER_TYPES = 'ORDER_TYPES';
    public const OTHER_ORDER_TYPES = 'OTHER_ORDER_TYPES';
    public const FEATURES = 'FEATURES';
    public const OTHER_FEATURES = 'OTHER_FEATURES';
    public const SPECIES_DOES = 'SPECIES_DOES';
    public const SPECIES_DOESNT = 'SPECIES_DOESNT';

    public const URL_FURSUITREVIEW = 'URL_FURSUITREVIEW';
    public const URL_WEBSITE = 'URL_WEBSITE';
    public const URL_PRICES = 'URL_PRICES';
    public const URL_FAQ = 'URL_FAQ';
    public const URL_FUR_AFFINITY = 'URL_FUR_AFFINITY';
    public const URL_DEVIANTART = 'URL_DEVIANTART';
    public const URL_TWITTER = 'URL_TWITTER';
    public const URL_FACEBOOK = 'URL_FACEBOOK';
    public const URL_TUMBLR = 'URL_TUMBLR';
    public const URL_INSTAGRAM = 'URL_INSTAGRAM';
    public const URL_YOUTUBE = 'URL_YOUTUBE';
    public const URL_QUEUE = 'URL_QUEUE';
    public const URL_SCRITCH = 'URL_SCRITCH';
    public const URL_SCRITCH_PHOTO = 'URL_SCRITCH_PHOTO';
    public const URL_SCRITCH_MINIATURE = 'URL_SCRITCH_MINIATURE';
    public const URL_OTHER = 'URL_OTHER';
    public const URL_CST = 'URL_CST';

    public const LANGUAGES = 'LANGUAGES';
    public const MAKER_ID = 'MAKER_ID';
    public const FORMER_MAKER_IDS = 'FORMER_MAKER_IDS';
    public const INTRO = 'INTRO';
    public const NOTES = 'NOTES';
    public const PASSCODE = 'PASSCODE';
    public const COMMISSIONS_STATUS = 'COMMISSIONS_STATUS';
    public const CST_LAST_CHECK = 'CST_LAST_CHECK';
    public const COMPLETNESS = 'COMPLETNESS';
    public const CONTACT_ALLOWED = 'CONTACT_ALLOWED';
    public const CONTACT_METHOD = 'CONTACT_METHOD';
    public const CONTACT_ADDRESS_PLAIN = 'CONTACT_ADDRESS_PLAIN';
    public const CONTACT_INFO_OBFUSCATED = 'CONTACT_INFO_OBFUSCATED';
    public const CONTACT_INFO_ORIGINAL = 'CONTACT_INFO_ORIGINAL';
    public const CONTACT_INPUT_VIRTUAL = 'CONTACT_INPUT_VIRTUAL';
    /***** "PRETTY" NAMES END *****/

    private const FIELDS_ARRAY_DATA = [
        /*                                                                           EXPORTED IN JSON? ----------.
         *                                                                              SHOW IN STATS? -------.  |
         *                                                                               IS PERSISTED? ----.  |  |
         *                                                                                    IS LIST? -.  |  |  |
         * PRETTY_NAME                   => ['model name (artisan)',     'validation regexp',           V  V  V  V
         */
        self::MAKER_ID                   => ['makerId',                  VR::MAKER_ID,                  0, 1, 1, 1],
        self::FORMER_MAKER_IDS           => ['formerMakerIds',           VR::FORMER_MAKER_IDS,          1, 1, 1, 1],
        self::NAME                       => ['name',                     VR::NON_EMPTY,                 0, 1, 1, 1],
        self::FORMERLY                   => ['formerly',                 VR::ANYTHING,                  1, 1, 1, 1],
        self::INTRO                      => ['intro',                    VR::ANYTHING,                  0, 1, 1, 1],
        self::SINCE                      => ['since',                    VR::SINCE,                     0, 1, 1, 1],
        self::COUNTRY                    => ['country',                  VR::COUNTRY,                   0, 1, 1, 1],
        self::STATE                      => ['state',                    VR::ANYTHING,                  0, 1, 1, 1],
        self::CITY                       => ['city',                     VR::ANYTHING,                  0, 1, 1, 1],
        self::PRODUCTION_MODELS          => ['productionModels',         VR::LIST_VALIDATION,           1, 1, 1, 1],
        self::STYLES                     => ['styles',                   VR::LIST_VALIDATION,           1, 1, 1, 1],
        self::OTHER_STYLES               => ['otherStyles',              VR::LIST_VALIDATION,           1, 1, 1, 1],
        self::ORDER_TYPES                => ['orderTypes',               VR::LIST_VALIDATION,           1, 1, 1, 1],
        self::OTHER_ORDER_TYPES          => ['otherOrderTypes',          VR::LIST_VALIDATION,           1, 1, 1, 1],
        self::FEATURES                   => ['features',                 VR::LIST_VALIDATION,           1, 1, 1, 1],
        self::OTHER_FEATURES             => ['otherFeatures',            VR::LIST_VALIDATION,           1, 1, 1, 1],
        self::PAYMENT_PLANS              => ['paymentPlans',             VR::ANYTHING,                  0, 1, 1, 1],
        self::SPECIES_DOES               => ['speciesDoes',              VR::ANYTHING,                  0, 1, 1, 1],
        self::SPECIES_DOESNT             => ['speciesDoesnt',            VR::ANYTHING,                  0, 1, 1, 1],
        self::URL_FURSUITREVIEW          => ['fursuitReviewUrl',         VR::FSR_URL,                   0, 1, 1, 1],
        self::URL_WEBSITE                => ['websiteUrl',               VR::GENERIC_URL,               0, 1, 1, 1],
        self::URL_PRICES                 => ['pricesUrl',                VR::GENERIC_URL,               0, 1, 1, 1],
        self::URL_FAQ                    => ['faqUrl',                   VR::GENERIC_URL,               0, 1, 1, 1],
        self::URL_FUR_AFFINITY           => ['furAffinityUrl',           VR::FA_URL,                    0, 1, 1, 1],
        self::URL_DEVIANTART             => ['deviantArtUrl',            VR::DA_URL,                    0, 1, 1, 1],
        self::URL_TWITTER                => ['twitterUrl',               VR::TWITTER_URL,               0, 1, 1, 1],
        self::URL_FACEBOOK               => ['facebookUrl',              VR::FACEBOOK_URL,              0, 1, 1, 1],
        self::URL_TUMBLR                 => ['tumblrUrl',                VR::TUMBLR_URL,                0, 1, 1, 1],
        self::URL_INSTAGRAM              => ['instagramUrl',             VR::INSTAGRAM_URL,             0, 1, 1, 1],
        self::URL_YOUTUBE                => ['youtubeUrl',               VR::YOUTUBE_URL,               0, 1, 1, 1],
        self::URL_QUEUE                  => ['queueUrl',                 VR::GENERIC_URL,               0, 1, 1, 1],
        self::URL_SCRITCH                => ['scritchUrl',               VR::SCRITCH_URL,               0, 1, 1, 1],
        self::URL_SCRITCH_PHOTO          => ['scritchPhotoUrls',         VR::SCRITCH_PHOTO_URLS,        1, 1, 1, 1],
        self::URL_SCRITCH_MINIATURE      => ['scritchMiniatureUrls',     VR::SCRITCH_MINIATURE_URLS,    1, 1, 1, 1],
        self::URL_OTHER                  => ['otherUrls',                VR::ANYTHING,                  0, 1, 1, 1],
        self::LANGUAGES                  => ['languages',                VR::ANYTHING,                  1, 1, 1, 1],
        self::NOTES                      => ['notes',                    VR::ANYTHING,                  0, 1, 0, 1],
        self::URL_CST                    => ['cstUrl',                   VR::GENERIC_URL,               0, 1, 1, 1],
        self::COMMISSIONS_STATUS         => ['commissionsStatus',        null,                          0, 0, 0, 1],
        self::CST_LAST_CHECK             => ['cstLastCheck',             null,                          0, 0, 0, 1],
        self::COMPLETNESS                => ['completeness',             null,                          0, 0, 0, 1],
        self::CONTACT_ALLOWED            => ['contactAllowed',           null,                          0, 1, 0, 1],
        self::CONTACT_METHOD             => ['contactMethod',            null,                          0, 1, 0, 0],
        self::CONTACT_ADDRESS_PLAIN      => ['contactAddressPlain',      null,                          0, 1, 0, 0],
        self::CONTACT_INFO_ORIGINAL      => ['contactInfoOriginal',      null,                          0, 1, 0, 0],
        self::CONTACT_INFO_OBFUSCATED    => ['contactInfoObfuscated',    null,                          0, 1, 0, 1],
        self::PASSCODE                   => ['passcode',                 null,                          0, 1, 0, 0],
        self::TIMESTAMP                  => [null,                       null,                          0, 0, 0, 0],
        self::VALIDATION_CHECKBOX        => [null,                       null,                          0, 0, 0, 0],
        self::CONTACT_INPUT_VIRTUAL      => [null,                       null,                          0, 0, 0, 0],
    ];

    private const IU_FORM_FIELDS_ORDERED = [
        /*                                                EXPORT TO I/U FORM ----.
         *                                              IMPORT FROM I/U FORM -.  |
         * PRETTY_NAME              => ['regexp 4 name in form'               V  V
         */
        self::TIMESTAMP             => [null,                                 0, 0],
        self::VALIDATION_CHECKBOX   => ['#update#',                           0, 1],
        self::NAME                  => ['#studio/maker\'s name#i',            1, 1],
        self::FORMERLY              => ['#formerly#i',                        1, 1],
        self::SINCE                 => ['#since when#i',                      1, 1],
        self::COUNTRY               => ['#country#i',                         1, 1],
        self::STATE                 => ['#what state is it in#i',             1, 1],
        self::CITY                  => ['#city#i',                            1, 1],
        self::PAYMENT_PLANS         => ['#payment plans#i',                   1, 1],
        self::URL_PRICES            => ['#prices list#i',                     1, 1],
        self::PRODUCTION_MODELS     => ['#What do you do#i',                  1, 1],
        self::STYLES                => ['#What styles#i',                     1, 1],
        self::OTHER_STYLES          => ['#Any other styles#i',                1, 1],
        self::ORDER_TYPES           => ['#What kind of#i',                    1, 1],
        self::OTHER_ORDER_TYPES     => ['#Any other kinds/items#i',           1, 1],
        self::FEATURES              => ['#What features#i',                   1, 1],
        self::OTHER_FEATURES        => ['#Any other features#i',              1, 1],
        self::SPECIES_DOES          => ['#What species#i',                    1, 1],
        self::SPECIES_DOESNT        => ['#species you will NOT#i',            1, 1],
        self::URL_FURSUITREVIEW     => ['#fursuitreview#i',                   1, 1],
        self::URL_WEBSITE           => ['#regular website#i',                 1, 1],
        self::URL_FAQ               => ['#FAQ#i',                             1, 1],
        self::URL_QUEUE             => ['#queue/progress#i',                  1, 1],
        self::URL_FUR_AFFINITY      => ['#FurAffinity#i',                     1, 1],
        self::URL_DEVIANTART        => ['#DeviantArt#i',                      1, 1],
        self::URL_TWITTER           => ['#Twitter#i',                         1, 1],
        self::URL_FACEBOOK          => ['#Facebook#i',                        1, 1],
        self::URL_TUMBLR            => ['#Tumblr#i',                          1, 1],
        self::URL_INSTAGRAM         => ['#Instagram#i',                       1, 1],
        self::URL_YOUTUBE           => ['#YouTube#i',                         1, 1],
        self::URL_OTHER             => ['#other websites#i',                  1, 1],
        self::URL_CST               => ['#commissions status#i',              1, 1],
        self::URL_SCRITCH           => ['#Got scritch\.es\?#i',               1, 1],
        self::URL_SCRITCH_PHOTO     => ['#"featured" photos#i',               1, 1],
        self::LANGUAGES             => ['#languages#i',                       1, 1],
        self::MAKER_ID              => ['#Maker ID#i',                        1, 1],
        self::INTRO                 => ['#intro#i',                           1, 1],
        self::NOTES                 => ['#notes#i',                           1, 1],
        self::PASSCODE              => ['#passcode#i',                        1, 0],
        self::CONTACT_ALLOWED       => ['#Permit to contact#i',               1, 1],
        self::CONTACT_INPUT_VIRTUAL => ['#How can I contact#i',               1, 1],
    ];

    private static $fields;
    private static $fieldsByModelName;

    public static function init()
    {
        self::$fields = [];

        foreach (self::FIELDS_ARRAY_DATA as $name => $fieldData) {
            $uiFormIndex = self::getUiFormIndexByFieldName($name);
            $iuFormRegexp = self::IU_FORM_FIELDS_ORDERED[$name][0] ?? null;
            $importFromIuForm = (bool) (self::IU_FORM_FIELDS_ORDERED[$name][1] ?? false);
            $exportFromIuForm = (bool) (self::IU_FORM_FIELDS_ORDERED[$name][2] ?? false);

            $field = new Field($name, $fieldData[0], $fieldData[1], $fieldData[2], $fieldData[3], $fieldData[4],
                $fieldData[5], $uiFormIndex, $iuFormRegexp, $importFromIuForm, $exportFromIuForm);

            self::$fields[$field->name()] = $field;
            self::$fieldsByModelName[$field->modelName()] = $field;
        }
    }

    public static function get(string $name): Field
    {
        if (!array_key_exists($name, self::$fields)) {
            throw new InvalidArgumentException("No such field exists: $name");
        }

        return self::$fields[$name];
    }

    public static function getByModelName(string $modelName): Field
    {
        if (!array_key_exists($modelName, self::$fieldsByModelName)) {
            throw new InvalidArgumentException("No field with such model name exists: $modelName");
        }

        return self::$fieldsByModelName[$modelName];
    }

    public static function uiFormIndex(string $name): int
    {
        return self::get($name)->uiFormIndex();
    }

    /**
     * @return Field[]
     */
    public static function persisted(): array
    {
        return array_filter(self::$fields, function (Field $field): bool {
            return $field->isPersisted();
        });
    }

    /**
     * @return Field[]
     */
    public static function inJson(): array
    {
        return array_filter(self::$fields, function (Field $field): bool {
            return $field->inJson();
        });
    }

    /**
     * @return Field[]
     */
    public static function inStats(): array
    {
        return array_filter(self::$fields, function (Field $field): bool {
            return $field->inStats();
        });
    }

    /**
     * @return Field[]
     */
    public static function lists(): array
    {
        return array_filter(self::$fields, function (Field $field): bool {
            return $field->isList();
        });
    }

    /**
     * @return Field[]
     */
    public static function inIuForm(): array
    {
        return array_filter(self::$fields, function (Field $field): bool {
            return $field->inIuForm();
        });
    }

    /**
     * @return Field[]
     */
    public static function exportedToIuForm(): array
    {
        return array_filter(self::$fields, function (Field $field): bool {
            return $field->exportToIuForm();
        });
    }

    /**
     * @return Field[]
     */
    public static function importedFromIuForm(): array
    {
        return array_filter(self::$fields, function (Field $field): bool {
            return $field->importFromIuForm();
        });
    }

    /**
     * @return Field[]
     */
    public static function urls(): array
    {
        return array_filter(self::$fields, function (Field $field): bool {
            return in_array($field->name(), [
                self::URL_FURSUITREVIEW,
                self::URL_WEBSITE,
                self::URL_PRICES,
                self::URL_FAQ,
                self::URL_FUR_AFFINITY,
                self::URL_DEVIANTART,
                self::URL_TWITTER,
                self::URL_FACEBOOK,
                self::URL_TUMBLR,
                self::URL_INSTAGRAM,
                self::URL_YOUTUBE,
                self::URL_QUEUE,
                self::URL_SCRITCH,
                self::URL_SCRITCH_PHOTO,
                self::URL_OTHER,
                self::URL_CST,
            ]);
        });
    }

    private static function getUiFormIndexByFieldName(string $fieldName): ?int
    {
        $result = array_search($fieldName, array_keys(self::IU_FORM_FIELDS_ORDERED), true);

        return false === $result ? null : $result;
    }
}

Fields::init();
