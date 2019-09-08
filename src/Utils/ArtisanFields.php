<?php

declare(strict_types=1);

namespace App\Utils;

use InvalidArgumentException;

class ArtisanFields
{
    private const GENERIC_URL_REGEXP = '#^(https?://[^/]+/.*)?$#'; // TODO: improve
    private const DA_URL_REGEXP = '#^(https://www\.deviantart\.com/[^/]+|https://[^.]+\.deviantart\.com/)?$#';
    private const FACEBOOK_URL_REGEXP = '#^(https://www.facebook.com/([^/]+/|profile\.php\?id=\d+))?$#';
    private const FSR_URL_REGEXP = '#^(http://fursuitreview.com/maker/[^/]+/)?$#';
    private const FA_URL_REGEXP = '#^(http://www\.furaffinity\.net/user/[^/]+)?$#';
    private const YOUTUBE_URL_REGEXP = '#^(https://www\.youtube\.com/(channel|user|c)/[^/?]+)?$#';
    private const INSTAGRAM_URL_REGEXP = '#^(https://www\.instagram\.com/[^/]+/)?$#';
    private const TUMBLR_URL_REGEXP = '#^(https?://[^.]+\.tumblr\.com/)?$#';
    private const TWITTER_URL_REGEXP = '#^(https://twitter\.com/[^/]+)?$#';

    private const LIST_VALIDATION_REGEXP = '#^[-,&!.A-Za-z0-9+()/\n %:"\']*$#';
    private const FORMER_MAKER_IDS_REGEXP = '#^([A-Z0-9]{7}(\n[A-Z0-9]{7})*)?$#';
    private const ANYTHING_REGEXP = '#^.*$#s';

    const IGNORED_IU_FORM_FIELD = ':ignore!';

    /***** "PRETTY" NAMES START *****/
    const TIMESTAMP = 'TIMESTAMP';
    const NAME = 'NAME';
    const FORMERLY = 'FORMERLY';
    const SINCE = 'SINCE';
    const COUNTRY = 'COUNTRY';
    const STATE = 'STATE';
    const CITY = 'CITY';
    const PAYMENT_PLANS = 'PAYMENT_PLANS';
    const URL_PRICES = 'URL_PRICES';
    const PRODUCTION_MODELS = 'PRODUCTION_MODELS';
    const STYLES = 'STYLES';
    const OTHER_STYLES = 'OTHER_STYLES';
    const ORDER_TYPES = 'ORDER_TYPES';
    const OTHER_ORDER_TYPES = 'OTHER_ORDER_TYPES';
    const FEATURES = 'FEATURES';
    const OTHER_FEATURES = 'OTHER_FEATURES';
    const SPECIES_DOES = 'SPECIES_DOES';
    const SPECIES_DOESNT = 'SPECIES_DOESNT';
    const URL_FSR = 'URL_FSR';
    const URL_WEBSITE = 'URL_WEBSITE';
    const URL_FAQ = 'URL_FAQ';
    const URL_QUEUE = 'URL_QUEUE';
    const URL_FA = 'URL_FA';
    const URL_DA = 'URL_DA';
    const URL_TWITTER = 'URL_TWITTER';
    const URL_FACEBOOK = 'URL_FACEBOOK';
    const URL_TUMBLR = 'URL_TUMBLR';
    const URL_INSTAGRAM = 'URL_INSTAGRAM';
    const URL_YOUTUBE = 'URL_YOUTUBE';
    const URL_OTHER = 'URL_OTHER';
    const URL_CST = 'URL_CST';
    const LANGUAGES = 'LANGUAGES';
    const MAKER_ID = 'MAKER_ID';
    const FORMER_MAKER_IDS = 'FORMER_MAKER_IDS';
    const INTRO = 'INTRO';
    const NOTES = 'NOTES';
    const PASSCODE = 'PASSCODE';
    const COMMISSIONS_STATUS = 'COMMISSIONS_STATUS';
    const CST_LAST_CHECK = 'CST_LAST_CHECK';
    const COMPLETNESS = 'COMPLETNESS';
    const CONTACT_ALLOWED = 'CONTACT_ALLOWED';
    const CONTACT_METHOD = 'CONTACT_METHOD';
    const CONTACT_ADDRESS_PLAIN = 'CONTACT_ADDRESS_PLAIN';
    const CONTACT_ADDRESS_OBFUSCATED = 'CONTACT_ADDRESS_OBFUSCATED';
    const ORIGINAL_CONTACT_INFO = 'ORIGINAL_CONTACT_INFO';
    /***** "PRETTY" NAMES END *****/

    private const FIELDS_ARRAY_DATA = [
        /*                                                                           EXPORTED IN JSON? ----------.
         *                                                                              SHOW IN STATS? -------.  |
         *                                                                               IS PERSISTED? ----.  |  |
         *                                                                                    IS LIST? -.  |  |  |
         * PRETTY_NAME                   => ['model name (artisan)',     'validation regexp',           V  V  V  V
         */
        self::MAKER_ID                   => ['makerId',                  '#^([A-Z0-9]{7})?$#',          0, 1, 1, 1],
        self::FORMER_MAKER_IDS           => ['formerMakerIds',           self::FORMER_MAKER_IDS_REGEXP, 1, 1, 1, 1],
        self::NAME                       => ['name',                     '#^.+$#',                      0, 1, 1, 1],
        self::FORMERLY                   => ['formerly',                 self::ANYTHING_REGEXP,         1, 1, 1, 1],
        self::INTRO                      => ['intro',                    self::ANYTHING_REGEXP,         0, 1, 1, 1],
        self::SINCE                      => ['since',                    '#^(\d{4}-\d{2})?$#',          0, 1, 1, 1],
        self::COUNTRY                    => ['country',                  '#^([A-Z]{2})?$#',             0, 1, 1, 1],
        self::STATE                      => ['state',                    self::ANYTHING_REGEXP,         0, 1, 1, 1],
        self::CITY                       => ['city',                     self::ANYTHING_REGEXP,         0, 1, 1, 1],
        self::PRODUCTION_MODELS          => ['productionModels',         self::LIST_VALIDATION_REGEXP,  1, 1, 1, 1],
        self::STYLES                     => ['styles',                   self::LIST_VALIDATION_REGEXP,  1, 1, 1, 1],
        self::OTHER_STYLES               => ['otherStyles',              self::LIST_VALIDATION_REGEXP,  1, 1, 1, 1],
        self::ORDER_TYPES                => ['orderTypes',               self::LIST_VALIDATION_REGEXP,  1, 1, 1, 1],
        self::OTHER_ORDER_TYPES          => ['otherOrderTypes',          self::LIST_VALIDATION_REGEXP,  1, 1, 1, 1],
        self::FEATURES                   => ['features',                 self::LIST_VALIDATION_REGEXP,  1, 1, 1, 1],
        self::OTHER_FEATURES             => ['otherFeatures',            self::LIST_VALIDATION_REGEXP,  1, 1, 1, 1],
        self::PAYMENT_PLANS              => ['paymentPlans',             self::ANYTHING_REGEXP,         0, 1, 1, 1],
        self::SPECIES_DOES               => ['speciesDoes',              self::ANYTHING_REGEXP,         0, 1, 1, 1],
        self::SPECIES_DOESNT             => ['speciesDoesnt',            self::ANYTHING_REGEXP,         0, 1, 1, 1],
        self::URL_FSR                    => ['fursuitReviewUrl',         self::FSR_URL_REGEXP,          0, 1, 1, 1],
        self::URL_WEBSITE                => ['websiteUrl',               self::GENERIC_URL_REGEXP,      0, 1, 1, 1],
        self::URL_PRICES                 => ['pricesUrl',                self::GENERIC_URL_REGEXP,      0, 1, 1, 1],
        self::URL_FAQ                    => ['faqUrl',                   self::GENERIC_URL_REGEXP,      0, 1, 1, 1],
        self::URL_FA                     => ['furAffinityUrl',           self::FA_URL_REGEXP,           0, 1, 1, 1],
        self::URL_DA                     => ['deviantArtUrl',            self::DA_URL_REGEXP,           0, 1, 1, 1],
        self::URL_TWITTER                => ['twitterUrl',               self::TWITTER_URL_REGEXP,      0, 1, 1, 1],
        self::URL_FACEBOOK               => ['facebookUrl',              self::FACEBOOK_URL_REGEXP,     0, 1, 1, 1],
        self::URL_TUMBLR                 => ['tumblrUrl',                self::TUMBLR_URL_REGEXP,       0, 1, 1, 1],
        self::URL_INSTAGRAM              => ['instagramUrl',             self::INSTAGRAM_URL_REGEXP,    0, 1, 1, 1],
        self::URL_YOUTUBE                => ['youtubeUrl',               self::YOUTUBE_URL_REGEXP,      0, 1, 1, 1],
        self::URL_QUEUE                  => ['queueUrl',                 self::GENERIC_URL_REGEXP,      0, 1, 1, 1],
        self::URL_OTHER                  => ['otherUrls',                self::ANYTHING_REGEXP,         0, 1, 1, 1],
        self::LANGUAGES                  => ['languages',                self::ANYTHING_REGEXP,         0, 1, 1, 1],
        self::NOTES                      => ['notes',                    '#.*#',                        0, 1, 0, 1],
        self::URL_CST                    => ['cstUrl',                   self::GENERIC_URL_REGEXP,      0, 1, 1, 1],
        self::COMMISSIONS_STATUS         => ['commissionsStatus',        null,                          0, 0, 0, 1],
        self::CST_LAST_CHECK             => ['cstLastCheck',             null,                          0, 0, 0, 1],
        self::COMPLETNESS                => ['completeness',             null,                          0, 0, 0, 1],
        self::CONTACT_ALLOWED            => ['contactAllowed',           null,                          0, 1, 0, 1],
        self::ORIGINAL_CONTACT_INFO      => ['originalContactInfo',      null,                          0, 1, 0, 0],
        self::CONTACT_METHOD             => ['contactMethod',            null,                          0, 1, 0, 0],
        self::CONTACT_ADDRESS_PLAIN      => ['contactAddressPlain',      null,                          0, 1, 0, 0],
        self::CONTACT_ADDRESS_OBFUSCATED => ['contactAddressObfuscated', null,                          0, 1, 0, 1],
        self::PASSCODE                   => ['passcode',                 null,                          0, 1, 0, 0],
        self::TIMESTAMP                  => [null,                       null,                          0, 0, 0, 0],
        self::IGNORED_IU_FORM_FIELD      => [null,                       null,                          0, 0, 0, 0],
    ];

    private const IU_FORM_FIELDS_ORDER = [
        self::TIMESTAMP, // Timestamp
        self::IGNORED_IU_FORM_FIELD, // Checkbox
        self::NAME,
        self::FORMERLY,
        self::SINCE,
        self::COUNTRY,
        self::STATE,
        self::CITY,
        self::PAYMENT_PLANS,
        self::URL_PRICES,
        self::PRODUCTION_MODELS,
        self::STYLES,
        self::OTHER_STYLES,
        self::ORDER_TYPES,
        self::OTHER_ORDER_TYPES,
        self::FEATURES,
        self::OTHER_FEATURES,
        self::SPECIES_DOES,
        self::SPECIES_DOESNT,
        self::URL_FSR,
        self::URL_WEBSITE,
        self::URL_FAQ,
        self::URL_QUEUE,
        self::URL_FA,
        self::URL_DA,
        self::URL_TWITTER,
        self::URL_FACEBOOK,
        self::URL_TUMBLR,
        self::URL_INSTAGRAM,
        self::URL_YOUTUBE,
        self::URL_OTHER,
        self::URL_CST,
        self::LANGUAGES,
        self::MAKER_ID,
        self::INTRO,
        self::NOTES,
        self::PASSCODE,
        self::CONTACT_ALLOWED,
        self::ORIGINAL_CONTACT_INFO,
    ];

    private static $fields;
    private static $fieldsByModelName;

    public static function init()
    {
        self::$fields = [];

        foreach (self::FIELDS_ARRAY_DATA as $name => $fieldData) {
            $field = new ArtisanField($name, $fieldData[0], $fieldData[1], $fieldData[2], $fieldData[3], $fieldData[4],
                $fieldData[5], self::getUiFormIndexByFieldName($name));

            self::$fields[$field->name()] = $field;
            self::$fieldsByModelName[$field->modelName()] = $field;
        }
    }

    public static function get(string $name): ArtisanField
    {
        if (!array_key_exists($name, self::$fields)) {
            throw new InvalidArgumentException("No such field exists: $name");
        }

        return self::$fields[$name];
    }

    public static function getByModelName(string $modelName): ArtisanField
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
     * @return ArtisanField[]
     */
    public static function persisted(): array
    {
        return array_filter(self::$fields, function (ArtisanField $field) {
            return $field->isPersisted();
        });
    }

    /**
     * @return ArtisanField[]
     */
    public static function inJson(): array
    {
        return array_filter(self::$fields, function (ArtisanField $field) {
            return $field->inJson();
        });
    }

    /**
     * @return ArtisanField[]
     */
    public static function inStats(): array
    {
        return array_filter(self::$fields, function (ArtisanField $field) {
            return $field->inStats();
        });
    }

    /**
     * @return ArtisanField[]
     */
    public static function lists(): array
    {
        return array_filter(self::$fields, function (ArtisanField $field) {
            return $field->isList();
        });
    }

    /**
     * @return ArtisanField[]
     */
    public static function inIuForm(): array
    {
        return array_filter(self::$fields, function (ArtisanField $field) {
            return in_array($field->name(), self::IU_FORM_FIELDS_ORDER);
        });
    }

    private static function getUiFormIndexByFieldName(string $fieldName): ?int
    {
        $result = array_search($fieldName, self::IU_FORM_FIELDS_ORDER, true);

        return false === $result ? null : $result;
    }
}

ArtisanFields::init();
