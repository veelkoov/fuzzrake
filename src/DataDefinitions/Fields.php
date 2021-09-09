<?php

declare(strict_types=1);

namespace App\DataDefinitions;

use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;

final class Fields
{
    use UtilityClass;

    public const MAKER_ID = 'MAKER_ID';
    public const FORMER_MAKER_IDS = 'FORMER_MAKER_IDS';

    public const NAME = 'NAME';
    public const FORMERLY = 'FORMERLY';

    public const INTRO = 'INTRO';
    public const SINCE = 'SINCE';

    public const LANGUAGES = 'LANGUAGES';
    public const COUNTRY = 'COUNTRY';
    public const STATE = 'STATE';
    public const CITY = 'CITY';

    public const PAYMENT_PLANS = 'PAYMENT_PLANS';
    public const PAYMENT_METHODS = 'PAYMENT_METHODS';
    public const CURRENCIES_ACCEPTED = 'CURRENCIES_ACCEPTED';

    public const PRODUCTION_MODELS_COMMENT = 'PRODUCTION_MODELS_COMMENT';
    public const PRODUCTION_MODELS = 'PRODUCTION_MODELS';

    public const STYLES_COMMENT = 'STYLES_COMMENT';
    public const STYLES = 'STYLES';
    public const OTHER_STYLES = 'OTHER_STYLES';

    public const ORDER_TYPES_COMMENT = 'ORDER_TYPES_COMMENT';
    public const ORDER_TYPES = 'ORDER_TYPES';
    public const OTHER_ORDER_TYPES = 'OTHER_ORDER_TYPES';

    public const FEATURES_COMMENT = 'FEATURES_COMMENT';
    public const FEATURES = 'FEATURES';
    public const OTHER_FEATURES = 'OTHER_FEATURES';

    public const SPECIES_COMMENT = 'SPECIES_COMMENT';
    public const SPECIES_DOES = 'SPECIES_DOES';
    public const SPECIES_DOESNT = 'SPECIES_DOESNT';

    public const IS_MINOR = 'IS_MINOR';
    public const WORKS_WITH_MINORS = 'WORKS_WITH_MINORS';

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
    public const URL_LINKLIST = 'URL_LINKLIST';
    public const URL_FURRY_AMINO = 'URL_FURRY_AMINO';
    public const URL_ETSY = 'URL_ETSY';
    public const URL_THE_DEALERS_DEN = 'URL_THE_DEALERS_DEN';
    public const URL_OTHER_SHOP = 'URL_OTHER_SHOP';
    public const URL_QUEUE = 'URL_QUEUE';
    public const URL_SCRITCH = 'URL_SCRITCH';
    public const URL_FURTRACK = 'URL_FURTRACK';
    public const URL_PHOTOS = 'URL_PHOTOS';
    public const URL_MINIATURES = 'URL_MINIATURES';
    public const URL_OTHER = 'URL_OTHER';
    public const URL_COMMISSIONS = 'URL_COMMISSIONS';

    public const NOTES = 'NOTES';
    public const INACTIVE_REASON = 'INACTIVE_REASON';
    public const PASSWORD = 'PASSWORD';
    public const CS_LAST_CHECK = 'CS_LAST_CHECK';
    public const CS_TRACKER_ISSUE = 'CS_TRACKER_ISSUE';
    public const BP_LAST_CHECK = 'BP_LAST_CHECK';
    public const BP_TRACKER_ISSUE = 'BP_TRACKER_ISSUE';
    public const OPEN_FOR = 'OPEN_FOR';
    public const CLOSED_FOR = 'CLOSED_FOR';
    public const COMPLETENESS = 'COMPLETENESS';

    public const CONTACT_ALLOWED = 'CONTACT_ALLOWED';
    public const CONTACT_METHOD = 'CONTACT_METHOD';
    public const CONTACT_ADDRESS_PLAIN = 'CONTACT_ADDRESS_PLAIN';
    public const CONTACT_INFO_OBFUSCATED = 'CONTACT_INFO_OBFUSCATED';
    public const CONTACT_INFO_ORIGINAL = 'CONTACT_INFO_ORIGINAL';

    private static ?array $fields = null;
    private static ?array $fieldsByModelName = null;

    private static ?FieldsList $all = null;
    private static ?FieldsList $persisted = null;
    private static ?FieldsList $public = null;
    private static ?FieldsList $inIuForm = null;
    private static ?FieldsList $inStats = null;
    private static ?FieldsList $lists = null;
    private static ?FieldsList $urls = null;
    private static ?FieldsList $none = null;

    public static function init()
    {
        self::$fields = [];
        self::$fieldsByModelName = [];

        foreach (FieldsDefinitions::FIELDS_ARRAY_DATA as $name => $fieldData) {
            $field = new Field($name, $fieldData[5], $fieldData[6], (bool) $fieldData[0], (bool) $fieldData[1],
                (bool) $fieldData[2], (bool) $fieldData[3], (bool) $fieldData[4]);

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

    public static function getAll(): FieldsList
    {
        return self::$all ??= new FieldsList(self::$fields);
    }

    public static function persisted(): FieldsList
    {
        return self::$persisted ??= self::getAll()->filtered(fn (Field $field): bool => $field->isPersisted());
    }

    public static function public(): FieldsList
    {
        return self::$public ??= self::getAll()->filtered(fn (Field $field): bool => $field->public());
    }

    public static function inIuForm(): FieldsList
    {
        return self::$inIuForm ??= self::getAll()->filtered(fn (Field $field): bool => $field->isInIuForm());
    }

    public static function inStats(): FieldsList
    {
        return self::$inStats ??= self::getAll()->filtered(fn (Field $field): bool => $field->inStats());
    }

    public static function lists(): FieldsList
    {
        return self::$lists ??= self::getAll()->filtered(fn (Field $field): bool => $field->isList());
    }

    public static function urls(): FieldsList
    {
        return self::$urls ??= self::getAll()->filtered(fn (Field $field): bool => in_array($field->name(), FieldsDefinitions::URLS));
    }

    public static function none(): FieldsList
    {
        return self::$none ??= new FieldsList([]);
    }
}

Fields::init();
