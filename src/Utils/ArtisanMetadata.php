<?php

declare(strict_types=1);

namespace App\Utils;

class ArtisanMetadata
{
    const IGNORED_IU_FORM_FIELD = ':ignore!';

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
    const PRODUCTION_MODEL = 'PRODUCTION_MODEL';
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
    const INTRO = 'INTRO';
    const NOTES = 'NOTES';
    const CONTACT_PERMIT = 'CONTACT_PERMIT';
    const CONTACT_METHOD = 'CONTACT_METHOD';

    const IU_FORM_TO_MODEL_FIELDS_MAP = [
        self::TIMESTAMP => self::IGNORED_IU_FORM_FIELD,
        self::CHECKBOX => self::IGNORED_IU_FORM_FIELD,
        self::NAME => 'name',
        self::FORMERLY => 'formerly',
        self::SINCE => 'since',
        self::COUNTRY => 'country',
        self::STATE => 'state',
        self::CITY => 'city',
        self::PAYMENT_PLANS => self::IGNORED_IU_FORM_FIELD,
        self::URL_PRICES => self::IGNORED_IU_FORM_FIELD,
        self::PRODUCTION_MODEL => self::IGNORED_IU_FORM_FIELD,
        self::STYLES => 'styles',
        self::OTHER_STYLES => 'otherStyles',
        self::ORDER_TYPES => 'types',
        self::OTHER_ORDER_TYPES => 'otherTypes',
        self::FEATURES => 'features',
        self::OTHER_FEATURES => 'otherFeatures',
        self::SPECIES_DOES => self::IGNORED_IU_FORM_FIELD,
        self::SPECIES_DOESNT => self::IGNORED_IU_FORM_FIELD,
        self::URL_FSR => 'fursuitReviewUrl',
        self::URL_WEBSITE => 'websiteUrl',
        self::URL_FAQ => self::IGNORED_IU_FORM_FIELD,
        self::URL_QUEUE => 'queueUrl',
        self::URL_FA => 'furAffinityUrl',
        self::URL_DA => 'deviantArtUrl',
        self::URL_TWITTER => 'twitterUrl',
        self::URL_FACEBOOK => 'facebookUrl',
        self::URL_TUMBLR => 'tumblrUrl',
        self::URL_INSTAGRAM => 'instagramUrl',
        self::URL_YOUTUBE => 'youtubeUrl',
        self::URL_OTHER => self::IGNORED_IU_FORM_FIELD,
        self::URL_CST => 'commisionsQuotesCheckUrl',
        self::LANGUAGES => self::IGNORED_IU_FORM_FIELD,
        self::MAKER_ID => self::IGNORED_IU_FORM_FIELD,
        self::INTRO => 'intro',
        self::NOTES => 'notes',
        self::CONTACT_PERMIT => self::IGNORED_IU_FORM_FIELD,
        self::CONTACT_METHOD => self::IGNORED_IU_FORM_FIELD,
    ];

    private static $uiFormFieldIndexes = [];
    private static $modelFieldNames = [];

    public static function uiFormFieldIndexByName(string $fieldName): int
    {
        return self::getUiFormIndexes()[$fieldName];
    }

    public static function getModelFieldNames(): array
    {
        if (empty(self::$modelFieldNames)) {
            self::initModelFieldNamesArray();
        }

        return self::$modelFieldNames;
    }

    private static function getUiFormIndexes(): array
    {
        if (empty(self::$uiFormFieldIndexes)) {
            self::initUiFormFieldIndexesArray();
        }

        return self::$uiFormFieldIndexes;
    }

    private static function initUiFormFieldIndexesArray(): void
    {
        $i = 0;

        foreach (self::IU_FORM_TO_MODEL_FIELDS_MAP as $fieldName => $_) {
            self::$uiFormFieldIndexes[$fieldName] = $i++;
        }
    }

    private static function initModelFieldNamesArray(): void
    {
        foreach (self::IU_FORM_TO_MODEL_FIELDS_MAP as $_ => $modelFieldName) {
            if ($modelFieldName !== ArtisanMetadata::IGNORED_IU_FORM_FIELD) {
                self::$modelFieldNames[] = $modelFieldName;
            }
        }
    }
}
