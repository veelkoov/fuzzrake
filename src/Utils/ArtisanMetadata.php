<?php

declare(strict_types=1);

namespace App\Utils;

class ArtisanMetadata
{
    const IGNORED_IU_FORM_FIELD = ':ignore!';

    const FIELDS = [
        'TIMESTAMP' => self::IGNORED_IU_FORM_FIELD,
        'CHECKBOX' => self::IGNORED_IU_FORM_FIELD,
        'NAME' => 'name',
        'FORMERLY' => 'formerly',
        'SINCE' => 'since',
        'COUNTRY' => 'country',
        'STATE' => 'state',
        'CITY' => 'city',
        'PAYMENT_PLANS' => self::IGNORED_IU_FORM_FIELD,
        'URL_PRICES' => self::IGNORED_IU_FORM_FIELD,
        'PRODUCTION_MODEL' => self::IGNORED_IU_FORM_FIELD,
        'STYLES' => 'styles',
        'OTHER_STYLES' => 'otherStyles',
        'ORDER_TYPES' => 'types',
        'OTHER_ORDER_TYPES' => 'otherTypes',
        'FEATURES' => 'features',
        'OTHER_FEATURES' => 'otherFeatures',
        'SPECIES_DOES' => self::IGNORED_IU_FORM_FIELD,
        'SPECIES_DOESNT' => self::IGNORED_IU_FORM_FIELD,
        'URL_FSR' => 'fursuitReviewUrl',
        'URL_WEBSITE' => 'websiteUrl',
        'URL_FAQ' => self::IGNORED_IU_FORM_FIELD,
        'URL_QUEUE' => 'queueUrl',
        'URL_FA' => 'furAffinityUrl',
        'URL_DA' => 'deviantArtUrl',
        'URL_TWITTER' => 'twitterUrl',
        'URL_FACEBOOK' => 'facebookUrl',
        'URL_TUMBLR' => 'tumblrUrl',
        'URL_INSTAGRAM' => 'instagramUrl',
        'URL_YOUTUBE' => 'youtubeUrl',
        'URL_OTHER' => self::IGNORED_IU_FORM_FIELD,
        'URL_CST' => 'commisionsQuotesCheckUrl',
        'LANGUAGES' => self::IGNORED_IU_FORM_FIELD,
        'MAKER_ID' => self::IGNORED_IU_FORM_FIELD,
        'INTRO' => 'intro',
        'NOTES' => 'notes',
        'CONTACT_PERMIT' => self::IGNORED_IU_FORM_FIELD,
        'CONTACT_METHOD' => self::IGNORED_IU_FORM_FIELD,
    ];

    private static $uiFormIndexes = [];

    public static function uiFormIdx(string $fieldName): int
    {
        return self::getUiFormIndexes()[$fieldName];
    }

    private static function getUiFormIndexes()
    {
        if (empty(self::$uiFormIndexes)) {
            self::initUiFormIndexesArray();
        }

        return self::$uiFormIndexes;
    }

    private static function initUiFormIndexesArray()
    {
        $i = 0;

        foreach (self::FIELDS as $fieldName => $_) {
            self::$uiFormIndexes[$fieldName] = $i++;
        }
    }
}
