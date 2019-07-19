<?php

declare(strict_types=1);

namespace App\Utils;

use InvalidArgumentException;

class ArtisanFields
{
    private const LIST_VALIDATION_REGEXP = '#^[-,&!.A-Za-z0-9+()/\n %:"\']*$#';
    private const GENERIC_URL_REGEXP = '#^(https?://[^/]+/.*)?$#'; // TODO: improve
    private const ANYTHING_REGEXP = '#^.*$#s';

    const IGNORED_IU_FORM_FIELD = ':ignore!';

    /***** "PRETTY" NAMES START *****/
    const TIMESTAMP = 'TIMESTAMP';
    const CHECKBOX = 'CHECKBOX';
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
    const CONTACT_PERMIT = 'CONTACT_PERMIT';
    const CONTACT_METHOD = 'CONTACT_METHOD';
    /***** "PRETTY" NAMES END *****/

    private const FIELDS_ARRAY_DATA = [
        /* PRETTY_NAME => ['model name', 'validation regexp', is_list_field] */
        self::NAME => ['name', '#^.+$#', false],
        self::FORMERLY => ['formerly', self::ANYTHING_REGEXP, true],
        self::SINCE => ['since', '#^(\d{4}-\d{2})?$#', false],
        self::COUNTRY => ['country', '#^([A-Z]{2})?$#', false],
        self::STATE => ['state', self::ANYTHING_REGEXP, false],
        self::CITY => ['city', self::ANYTHING_REGEXP, false],
        self::PAYMENT_PLANS => ['paymentPlans', self::ANYTHING_REGEXP, false],
        self::URL_PRICES => ['pricesUrl', self::GENERIC_URL_REGEXP, false],
        self::PRODUCTION_MODELS => ['productionModels', self::LIST_VALIDATION_REGEXP, true],
        self::STYLES => ['styles', self::LIST_VALIDATION_REGEXP, true],
        self::OTHER_STYLES => ['otherStyles', self::LIST_VALIDATION_REGEXP, true],
        self::ORDER_TYPES => ['orderTypes', self::LIST_VALIDATION_REGEXP, true],
        self::OTHER_ORDER_TYPES => ['otherOrderTypes', self::LIST_VALIDATION_REGEXP, true],
        self::FEATURES => ['features', self::LIST_VALIDATION_REGEXP, true],
        self::OTHER_FEATURES => ['otherFeatures', self::LIST_VALIDATION_REGEXP, true],
        self::SPECIES_DOES => ['speciesDoes', self::ANYTHING_REGEXP, false],
        self::SPECIES_DOESNT => ['speciesDoesnt', self::ANYTHING_REGEXP, false],
        self::URL_FSR => ['fursuitReviewUrl', '#^(http://fursuitreview.com/maker/[^/]+/)?$#', false],
        self::URL_WEBSITE => ['websiteUrl', self::GENERIC_URL_REGEXP, false],
        self::URL_FAQ => ['faqUrl', self::GENERIC_URL_REGEXP, false],
        self::URL_QUEUE => ['queueUrl', self::GENERIC_URL_REGEXP, false],
        self::URL_FA => ['furAffinityUrl', '#^(http://www\.furaffinity\.net/user/[^/]+)?$#', false],
        self::URL_DA => ['deviantArtUrl', '#^(https://www\.deviantart\.com/[^/]+|https://[^.]+\.deviantart\.com/)?$#', false],
        self::URL_TWITTER => ['twitterUrl', '#^(https://twitter\.com/[^/]+)?$#', false],
        self::URL_FACEBOOK => ['facebookUrl', '#^(https://www.facebook.com/([^/]+/|profile\.php\?id=\d+))?$#', false],
        self::URL_TUMBLR => ['tumblrUrl', '#^(https?://[^.]+\.tumblr\.com/)?$#', false],
        self::URL_INSTAGRAM => ['instagramUrl', '#^(https://www\.instagram\.com/[^/]+/)?$#', false],
        self::URL_YOUTUBE => ['youtubeUrl', '#^(https://www\.youtube\.com/(channel|user|c)/[^/?]+)?$#', false],
        self::URL_OTHER => ['otherUrls', self::ANYTHING_REGEXP, false],
        self::URL_CST => ['cstUrl', self::GENERIC_URL_REGEXP, false],
        self::LANGUAGES => ['languages', self::ANYTHING_REGEXP, false],
        self::MAKER_ID => ['makerId', '#^([A-Z0-9]{7})?$#', false],
        self::FORMER_MAKER_IDS => ['formerMakerIds', '#^([A-Z0-9]{7}(\n[A-Z0-9]{7})*)?$#', true],
        self::INTRO => ['intro', self::ANYTHING_REGEXP, false],
        self::NOTES => ['notes', '#.*#', false],

        self::TIMESTAMP => [null, null, null],
        self::PASSCODE => [null, null, null],
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
        self::PASSCODE, // Passcode
        self::IGNORED_IU_FORM_FIELD, // Contact permit
        self::IGNORED_IU_FORM_FIELD, // Contact method
    ];

    private static $fields;
    private static $fieldsByModelName;

    public static function init()
    {
        self::$fields = [];

        foreach (self::FIELDS_ARRAY_DATA as $name => $fieldData) {
            $field = new ArtisanField($name, $fieldData[0], $fieldData[1], $fieldData[2],
                self::getUiFormIndexByFieldName($name));

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
        if (!array_key_exists($modelName, self::$fields)) {
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
    public static function lists(): array
    {
        return array_filter(self::$fields, function (ArtisanField $field) {
            return $field->isList();
        });
    }

    private static function getUiFormIndexByFieldName(string $fieldName): ?int
    {
        $result = array_search($fieldName, self::IU_FORM_FIELDS_ORDER, true);

        return false === $result ? null : $result;
    }
}

ArtisanFields::init();
