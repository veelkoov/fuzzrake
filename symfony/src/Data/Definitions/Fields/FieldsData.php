<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use App\Data\Definitions\Fields\ValidationRegexps as V;
use App\Utils\Traits\UtilityClass;
use TRegx\CleanRegex\Pattern;

final class FieldsData
{
    use UtilityClass;

    public const DATA = [
        Field::MAKER_ID->value => [
            'modelName'        => 'makerId',
            'validationRegex'  => V::MAKER_ID,
        ],
        Field::FORMER_MAKER_IDS->value => [
            'modelName'        => 'formerMakerIds',
            'type'             => Type::STR_LIST,
            'inIuForm'         => false,
            'freeForm'         => false,
            'affectedByIuForm' => true,
            'validationRegex'  => V::FORMER_MAKER_IDS,
        ],
        Field::NAME->value => [
            'modelName'        => 'name',
            'validationRegex'  => V::NON_EMPTY,
        ],
        Field::FORMERLY->value => [
            'modelName'        => 'formerly',
            'type'             => Type::STR_LIST,
        ],
        Field::DATE_ADDED->value => [
            'modelName'        => 'dateAdded',
            'type'             => Type::DATE,
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'affectedByIuForm' => true,
        ],
        Field::DATE_UPDATED->value => [
            'modelName'        => 'dateUpdated',
            'type'             => Type::DATE,
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'affectedByIuForm' => true,
        ],
        Field::INTRO->value => [
            'modelName'        => 'intro',
        ],
        Field::SINCE->value => [
            'modelName'        => 'since',
            'freeForm'         => false,
            'validationRegex'  => V::SINCE,
        ],
        Field::LANGUAGES->value => [
            'modelName'        => 'languages',
            'type'             => Type::STR_LIST,
        ],
        Field::COUNTRY->value => [
            'modelName'        => 'country',
            'freeForm'         => false,
            'validationRegex'  => V::COUNTRY,
        ],
        Field::STATE->value => [
            'modelName'        => 'state',
            'validationRegex'  => V::STATE,
        ],
        Field::CITY->value => [
            'modelName'        => 'city',
        ],
        Field::PRODUCTION_MODELS_COMMENT->value => [
            'modelName'        => 'productionModelsComment',
            'inStats'          => false,
        ],
        Field::PRODUCTION_MODELS->value => [
            'modelName'        => 'productionModels',
            'type'             => Type::STR_LIST,
            'freeForm'         => false,
            'validationRegex'  => V::LIST_VALIDATION,
        ],
        Field::STYLES_COMMENT->value => [
            'modelName'        => 'stylesComment',
            'inStats'          => false,
        ],
        Field::STYLES->value => [
            'modelName'        => 'styles',
            'type'             => Type::STR_LIST,
            'freeForm'         => false,
            'validationRegex'  => V::LIST_VALIDATION,
        ],
        Field::OTHER_STYLES->value => [
            'modelName'        => 'otherStyles',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::LIST_VALIDATION,
        ],
        Field::ORDER_TYPES_COMMENT->value => [
            'modelName'        => 'orderTypesComment',
            'inStats'          => false,
        ],
        Field::ORDER_TYPES->value => [
            'modelName'        => 'orderTypes',
            'type'             => Type::STR_LIST,
            'freeForm'         => false,
            'validationRegex'  => V::LIST_VALIDATION,
        ],
        Field::OTHER_ORDER_TYPES->value => [
            'modelName'        => 'otherOrderTypes',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::LIST_VALIDATION,
        ],
        Field::FEATURES_COMMENT->value => [
            'modelName'        => 'featuresComment',
            'inStats'          => false,
        ],
        Field::FEATURES->value => [
            'modelName'        => 'features',
            'type'             => Type::STR_LIST,
            'freeForm'         => false,
            'validationRegex'  => V::LIST_VALIDATION,
        ],
        Field::OTHER_FEATURES->value => [
            'modelName'        => 'otherFeatures',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::LIST_VALIDATION,
        ],
        Field::PAYMENT_PLANS->value => [
            'modelName'        => 'paymentPlans',
            'type'             => Type::STR_LIST,
        ],
        Field::PAYMENT_METHODS->value => [
            'modelName'        => 'paymentMethods',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::PAY_METHODS,
        ],
        Field::CURRENCIES_ACCEPTED->value => [
            'modelName'        => 'currenciesAccepted',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::CURRENCIES,
        ],
        Field::SPECIES_COMMENT->value => [
            'modelName'        => 'speciesComment',
        ],
        Field::SPECIES_DOES->value => [
            'modelName'        => 'speciesDoes',
            'type'             => Type::STR_LIST,
        ],
        Field::SPECIES_DOESNT->value => [
            'modelName'        => 'speciesDoesnt',
            'type'             => Type::STR_LIST,
        ],
        Field::IS_MINOR->value => [
            'modelName'        => 'isMinor',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::AGES->value => [
            'modelName'        => 'ages',
            'freeForm'         => false,
        ],
        Field::NSFW_WEBSITE->value => [
            'modelName'        => 'nsfwWebsite',
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::NSFW_SOCIAL->value => [
            'modelName'        => 'nsfwSocial',
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::DOES_NSFW->value => [
            'modelName'        => 'doesNsfw',
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::SAFE_DOES_NSFW->value => [
            'modelName'        => 'safeDoesNsfw',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'persisted'        => false,
        ],
        Field::WORKS_WITH_MINORS->value => [
            'modelName'        => 'worksWithMinors',
            'freeForm'         => false,
            'inStats'          => false,
            'public'           => false,
        ],
        Field::SAFE_WORKS_WITH_MINORS->value => [
            'modelName'        => 'safeWorksWithMinors',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'persisted'        => false,
        ],
        Field::URL_FURSUITREVIEW->value => [
            'modelName'        => 'fursuitReviewUrl',
            'validationRegex'  => V::FSR_URL,
        ],
        Field::URL_WEBSITE->value => [
            'modelName'        => 'websiteUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_PRICES->value => [
            'modelName'        => 'pricesUrls',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::GENERIC_URL_LIST,
        ],
        Field::URL_COMMISSIONS->value => [
            'modelName'        => 'commissionsUrls',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::GENERIC_URL_LIST,
        ],
        Field::URL_FAQ->value => [
            'modelName'        => 'faqUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_FUR_AFFINITY->value => [
            'modelName'        => 'furAffinityUrl',
            'validationRegex'  => V::FA_URL,
        ],
        Field::URL_DEVIANTART->value => [
            'modelName'        => 'deviantArtUrl',
            'validationRegex'  => V::DA_URL,
        ],
        Field::URL_MASTODON->value => [
            'modelName'        => 'mastodonUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_TWITTER->value => [
            'modelName'        => 'twitterUrl',
            'validationRegex'  => V::TWITTER_URL,
        ],
        Field::URL_FACEBOOK->value => [
            'modelName'        => 'facebookUrl',
            'validationRegex'  => V::FACEBOOK_URL,
        ],
        Field::URL_TUMBLR->value => [
            'modelName'        => 'tumblrUrl',
            'validationRegex'  => V::TUMBLR_URL,
        ],
        Field::URL_INSTAGRAM->value => [
            'modelName'        => 'instagramUrl',
            'validationRegex'  => V::INSTAGRAM_URL,
        ],
        Field::URL_YOUTUBE->value => [
            'modelName'        => 'youtubeUrl',
            'validationRegex'  => V::YOUTUBE_URL,
        ],
        Field::URL_LINKLIST->value => [
            'modelName'        => 'linklistUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_FURRY_AMINO->value => [
            'modelName'        => 'furryAminoUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_ETSY->value => [
            'modelName'        => 'etsyUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_THE_DEALERS_DEN->value => [
            'modelName'        => 'theDealersDenUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_OTHER_SHOP->value => [
            'modelName'        => 'otherShopUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_QUEUE->value => [
            'modelName'        => 'queueUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_SCRITCH->value => [
            'modelName'        => 'scritchUrl',
            'validationRegex'  => V::SCRITCH_URL,
        ],
        Field::URL_FURTRACK->value => [
            'modelName'        => 'furtrackUrl',
            'validationRegex'  => V::FURTRACK_URL,
        ],
        Field::URL_PHOTOS->value => [
            'modelName'        => 'photoUrls',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::PHOTO_URL_LIST,
            'notInspectedUrl'  => true,
        ],
        Field::URL_MINIATURES->value => [
            'modelName'        => 'miniatureUrls',
            'type'             => Type::STR_LIST,
            'inIuForm'         => false,
            'validationRegex'  => V::MINIATURE_URL_LIST,
            'notInspectedUrl'  => true,
        ],
        Field::URL_OTHER->value => [
            'modelName'        => 'otherUrls',
            'notInspectedUrl'  => true,
        ],
        Field::NOTES->value => [
            'modelName'        => 'notes',
            'inStats'          => false,
        ],
        Field::INACTIVE_REASON->value => [
            'modelName'        => 'inactiveReason',
            'inIuForm'         => false,
            'freeForm'         => false,
        ],
        Field::PASSWORD->value => [
            'modelName'        => 'password',
            'freeForm'         => false,
            'inStats'          => false,
            'public'           => false,
        ],
        Field::CS_LAST_CHECK->value => [
            'modelName'        => 'csLastCheck',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::CS_TRACKER_ISSUE->value => [
            'modelName'        => 'csTrackerIssue',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::OPEN_FOR->value => [
            'modelName'        => 'openFor',
            'type'             => Type::STR_LIST,
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::CLOSED_FOR->value => [
            'modelName'        => 'closedFor',
            'type'             => Type::STR_LIST,
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::COMPLETENESS->value => [
            'modelName'        => 'completeness',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'persisted'        => false,
        ],
        Field::CONTACT_ALLOWED->value => [
            'modelName'        => 'contactAllowed',
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::CONTACT_METHOD->value => [
            'modelName'        => 'contactMethod',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'public'           => false,
            'affectedByIuForm' => true,
        ],
        Field::CONTACT_ADDRESS_PLAIN->value => [
            'modelName'        => 'contactAddressPlain',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'public'           => false,
            'affectedByIuForm' => true,
        ],
        Field::CONTACT_INFO_OBFUSCATED->value => [
            'modelName'        => 'contactInfoObfuscated',
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::CONTACT_INFO_ORIGINAL->value => [
            'modelName'        => 'contactInfoOriginal',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'public'           => false,
            'affectedByIuForm' => true,
        ],
    ];

    /**
     * @var array<string, Pattern>
     */
    private static array $validationPatterns = [];

    public static function getModelName(Field $field): string
    {
        return self::DATA[$field->value]['modelName'];
    }

    public static function getType(Field $field): Type
    {
        return self::DATA[$field->value]['type'] ?? Type::STRING;
    }

    public static function isFreeForm(Field $field): bool
    {
        return self::DATA[$field->value]['freeForm'] ?? true;
    }

    public static function isInStats(Field $field): bool
    {
        return self::DATA[$field->value]['inStats'] ?? true;
    }

    public static function isPublic(Field $field): bool
    {
        return self::DATA[$field->value]['public'] ?? true;
    }

    public static function isInIuForm(Field $field): bool
    {
        return self::DATA[$field->value]['inIuForm'] ?? true;
    }

    public static function isPersisted(Field $field): bool
    {
        return self::DATA[$field->value]['persisted'] ?? true;
    }

    public static function isAffectedByIuForm(Field $field): bool
    {
        return self::DATA[$field->value]['affectedByIuForm'] ?? false;
    }

    public static function isNotInspectedUrl(Field $field): bool
    {
        return self::DATA[$field->value]['notInspectedUrl'] ?? false;
    }

    public static function isValidated(Field $field): bool
    {
        return null !== self::getValidationRegexp($field);
    }

    private static function getValidationRegexp(Field $field): ?string
    {
        return self::DATA[$field->value]['validationRegex'] ?? null;
    }

    public static function getValidationPattern(Field $field): ?Pattern
    {
        $regex = self::getValidationRegexp($field);

        if (null === $regex) {
            return null;
        }

        return self::$validationPatterns[$field->value] ??=pattern($regex, 'n');
    }
}
