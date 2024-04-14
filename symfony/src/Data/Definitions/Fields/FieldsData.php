<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use App\Data\Definitions\Fields\ValidationRegexps as V;
use App\Utils\Traits\UtilityClass;
use ReflectionEnum;
use UnexpectedValueException;

final class FieldsData
{
    use UtilityClass;

    private const DATA = [
        Field::MAKER_ID->name => [
            'modelName'        => 'makerId',
            'validationRegex'  => V::MAKER_ID,
        ],
        Field::FORMER_MAKER_IDS->name => [
            'modelName'        => 'formerMakerIds',
            'type'             => Type::STR_LIST,
            'inIuForm'         => false,
            'freeForm'         => false,
            'affectedByIuForm' => true,
            'validationRegex'  => V::FORMER_MAKER_IDS,
        ],
        Field::NAME->name => [
            'modelName'        => 'name',
            'validationRegex'  => V::NON_EMPTY,
        ],
        Field::FORMERLY->name => [
            'modelName'        => 'formerly',
            'type'             => Type::STR_LIST,
        ],
        Field::DATE_ADDED->name => [
            'modelName'        => 'dateAdded',
            'type'             => Type::DATE,
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'affectedByIuForm' => true,
        ],
        Field::DATE_UPDATED->name => [
            'modelName'        => 'dateUpdated',
            'type'             => Type::DATE,
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'affectedByIuForm' => true,
        ],
        Field::INTRO->name => [
            'modelName'        => 'intro',
        ],
        Field::SINCE->name => [
            'modelName'        => 'since',
            'freeForm'         => false,
            'validationRegex'  => V::SINCE,
        ],
        Field::LANGUAGES->name => [
            'modelName'        => 'languages',
            'type'             => Type::STR_LIST,
        ],
        Field::COUNTRY->name => [
            'modelName'        => 'country',
            'freeForm'         => false,
            'validationRegex'  => V::COUNTRY,
        ],
        Field::STATE->name => [
            'modelName'        => 'state',
            'validationRegex'  => V::STATE,
        ],
        Field::CITY->name => [
            'modelName'        => 'city',
        ],
        Field::PRODUCTION_MODELS_COMMENT->name => [
            'modelName'        => 'productionModelsComment',
            'inStats'          => false,
        ],
        Field::PRODUCTION_MODELS->name => [
            'modelName'        => 'productionModels',
            'type'             => Type::STR_LIST,
            'freeForm'         => false,
            'validationRegex'  => V::LIST_VALIDATION,
        ],
        Field::STYLES_COMMENT->name => [
            'modelName'        => 'stylesComment',
            'inStats'          => false,
        ],
        Field::STYLES->name => [
            'modelName'        => 'styles',
            'type'             => Type::STR_LIST,
            'freeForm'         => false,
            'validationRegex'  => V::LIST_VALIDATION,
        ],
        Field::OTHER_STYLES->name => [
            'modelName'        => 'otherStyles',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::LIST_VALIDATION,
        ],
        Field::ORDER_TYPES_COMMENT->name => [
            'modelName'        => 'orderTypesComment',
            'inStats'          => false,
        ],
        Field::ORDER_TYPES->name => [
            'modelName'        => 'orderTypes',
            'type'             => Type::STR_LIST,
            'freeForm'         => false,
            'validationRegex'  => V::LIST_VALIDATION,
        ],
        Field::OTHER_ORDER_TYPES->name => [
            'modelName'        => 'otherOrderTypes',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::LIST_VALIDATION,
        ],
        Field::FEATURES_COMMENT->name => [
            'modelName'        => 'featuresComment',
            'inStats'          => false,
        ],
        Field::FEATURES->name => [
            'modelName'        => 'features',
            'type'             => Type::STR_LIST,
            'freeForm'         => false,
            'validationRegex'  => V::LIST_VALIDATION,
        ],
        Field::OTHER_FEATURES->name => [
            'modelName'        => 'otherFeatures',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::LIST_VALIDATION,
        ],
        Field::PAYMENT_PLANS->name => [
            'modelName'        => 'paymentPlans',
            'type'             => Type::STR_LIST,
        ],
        Field::PAYMENT_METHODS->name => [
            'modelName'        => 'paymentMethods',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::PAY_METHODS,
        ],
        Field::CURRENCIES_ACCEPTED->name => [
            'modelName'        => 'currenciesAccepted',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::CURRENCIES,
        ],
        Field::SPECIES_COMMENT->name => [
            'modelName'        => 'speciesComment',
        ],
        Field::SPECIES_DOES->name => [
            'modelName'        => 'speciesDoes',
            'type'             => Type::STR_LIST,
        ],
        Field::SPECIES_DOESNT->name => [
            'modelName'        => 'speciesDoesnt',
            'type'             => Type::STR_LIST,
        ],
        Field::IS_MINOR->name => [
            'modelName'        => 'isMinor',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::AGES->name => [
            'modelName'        => 'ages',
            'freeForm'         => false,
        ],
        Field::NSFW_WEBSITE->name => [
            'modelName'        => 'nsfwWebsite',
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::NSFW_SOCIAL->name => [
            'modelName'        => 'nsfwSocial',
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::DOES_NSFW->name => [
            'modelName'        => 'doesNsfw',
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::SAFE_DOES_NSFW->name => [
            'modelName'        => 'safeDoesNsfw',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'persisted'        => false,
        ],
        Field::WORKS_WITH_MINORS->name => [
            'modelName'        => 'worksWithMinors',
            'freeForm'         => false,
            'inStats'          => false,
            'public'           => false,
        ],
        Field::SAFE_WORKS_WITH_MINORS->name => [
            'modelName'        => 'safeWorksWithMinors',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'persisted'        => false,
        ],
        Field::URL_FURSUITREVIEW->name => [
            'modelName'        => 'fursuitReviewUrl',
            'validationRegex'  => V::FSR_URL,
        ],
        Field::URL_WEBSITE->name => [
            'modelName'        => 'websiteUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_PRICES->name => [
            'modelName'        => 'pricesUrls',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::GENERIC_URL_LIST,
        ],
        Field::URL_COMMISSIONS->name => [
            'modelName'        => 'commissionsUrls',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::GENERIC_URL_LIST,
        ],
        Field::URL_FAQ->name => [
            'modelName'        => 'faqUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_FUR_AFFINITY->name => [
            'modelName'        => 'furAffinityUrl',
            'validationRegex'  => V::FA_URL,
        ],
        Field::URL_DEVIANTART->name => [
            'modelName'        => 'deviantArtUrl',
            'validationRegex'  => V::DA_URL,
        ],
        Field::URL_MASTODON->name => [
            'modelName'        => 'mastodonUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_TWITTER->name => [
            'modelName'        => 'twitterUrl',
            'validationRegex'  => V::TWITTER_URL,
        ],
        Field::URL_FACEBOOK->name => [
            'modelName'        => 'facebookUrl',
            'validationRegex'  => V::FACEBOOK_URL,
        ],
        Field::URL_TUMBLR->name => [
            'modelName'        => 'tumblrUrl',
            'validationRegex'  => V::TUMBLR_URL,
        ],
        Field::URL_INSTAGRAM->name => [
            'modelName'        => 'instagramUrl',
            'validationRegex'  => V::INSTAGRAM_URL,
        ],
        Field::URL_YOUTUBE->name => [
            'modelName'        => 'youtubeUrl',
            'validationRegex'  => V::YOUTUBE_URL,
        ],
        Field::URL_LINKLIST->name => [
            'modelName'        => 'linklistUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_FURRY_AMINO->name => [
            'modelName'        => 'furryAminoUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_ETSY->name => [
            'modelName'        => 'etsyUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_THE_DEALERS_DEN->name => [
            'modelName'        => 'theDealersDenUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_OTHER_SHOP->name => [
            'modelName'        => 'otherShopUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_QUEUE->name => [
            'modelName'        => 'queueUrl',
            'validationRegex'  => V::GENERIC_URL,
        ],
        Field::URL_SCRITCH->name => [
            'modelName'        => 'scritchUrl',
            'validationRegex'  => V::SCRITCH_URL,
        ],
        Field::URL_FURTRACK->name => [
            'modelName'        => 'furtrackUrl',
            'validationRegex'  => V::FURTRACK_URL,
        ],
        Field::URL_PHOTOS->name => [
            'modelName'        => 'photoUrls',
            'type'             => Type::STR_LIST,
            'validationRegex'  => V::PHOTO_URL_LIST,
            'notInspectedUrl'  => true,
        ],
        Field::URL_MINIATURES->name => [
            'modelName'        => 'miniatureUrls',
            'type'             => Type::STR_LIST,
            'inIuForm'         => false,
            'validationRegex'  => V::MINIATURE_URL_LIST,
            'notInspectedUrl'  => true,
        ],
        Field::URL_OTHER->name => [
            'modelName'        => 'otherUrls',
            'notInspectedUrl'  => true,
        ],
        Field::NOTES->name => [
            'modelName'        => 'notes',
            'inStats'          => false,
        ],
        Field::INACTIVE_REASON->name => [
            'modelName'        => 'inactiveReason',
            'inIuForm'         => false,
            'freeForm'         => false,
        ],
        Field::PASSWORD->name => [
            'modelName'        => 'password',
            'freeForm'         => false,
            'inStats'          => false,
            'public'           => false,
        ],
        Field::CS_LAST_CHECK->name => [
            'modelName'        => 'csLastCheck',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::CS_TRACKER_ISSUE->name => [
            'modelName'        => 'csTrackerIssue',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::OPEN_FOR->name => [
            'modelName'        => 'openFor',
            'type'             => Type::STR_LIST,
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::CLOSED_FOR->name => [
            'modelName'        => 'closedFor',
            'type'             => Type::STR_LIST,
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::COMPLETENESS->name => [
            'modelName'        => 'completeness',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'persisted'        => false,
        ],
        Field::CONTACT_ALLOWED->name => [
            'modelName'        => 'contactAllowed',
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::CONTACT_METHOD->name => [
            'modelName'        => 'contactMethod',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'public'           => false,
            'affectedByIuForm' => true,
        ],
        Field::CONTACT_ADDRESS_PLAIN->name => [
            'modelName'        => 'contactAddressPlain',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'public'           => false,
            'affectedByIuForm' => true,
        ],
        Field::CONTACT_INFO_OBFUSCATED->name => [
            'modelName'        => 'contactInfoObfuscated',
            'freeForm'         => false,
            'inStats'          => false,
        ],
        Field::CONTACT_INFO_ORIGINAL->name => [
            'modelName'        => 'contactInfoOriginal',
            'inIuForm'         => false,
            'freeForm'         => false,
            'inStats'          => false,
            'public'           => false,
            'affectedByIuForm' => true,
        ],
    ];

    /**
     * @var array<string, FieldData>
     */
    private static array $fieldsOld = [];

    /**
     * @var array<string, FieldData>
     */
    private static array $fields = [];

    public static function init(): void
    {
        self::$fieldsOld = [];

        foreach ((new ReflectionEnum(Field::class))->getCases() as $case) {
            if ($case->getBackingValue() !== $case->name) {
                throw new UnexpectedValueException('name !== value');
            }

            $fieldName = (string) ($case->getBackingValue());

            self::$fields[$fieldName] = new FieldData(
                $fieldName,
                self::DATA[$fieldName]['modelName'],
                self::DATA[$fieldName]['type'] ?? Type::STRING,
                self::DATA[$fieldName]['validationRegex'] ?? null,
                self::DATA[$fieldName]['freeForm'] ?? true,
                self::DATA[$fieldName]['inStats'] ?? true,
                self::DATA[$fieldName]['public'] ?? true,
                self::DATA[$fieldName]['inIuForm'] ?? true,
                self::DATA[$fieldName]['persisted'] ?? true,
                self::DATA[$fieldName]['affectedByIuForm'] ?? false,
                self::DATA[$fieldName]['notInspectedUrl'] ?? false,
            );

            foreach ($case->getAttributes() as $attribute) {
                /** @var Properties $data */
                $data = $attribute->newInstance();

                self::$fieldsOld[$fieldName] = new FieldData(
                    $fieldName,
                    $data->modelName,
                    $data->type,
                    $data->validationRegex,
                    $data->freeForm,
                    $data->inStats,
                    $data->public,
                    $data->inIuForm,
                    $data->persisted,
                    $data->affectedByIuForm,
                    $data->notInspectedUrl,
                );
            }
        }
    }

    public static function get(Field $field): FieldData
    {
        return self::$fieldsOld[$field->value];
    }
}

FieldsData::init();
