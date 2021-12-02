<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\DataDefinitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\Fixer\ContactAllowedFixer;
use App\Utils\Data\Fixer\CountryFixer;
use App\Utils\Data\Fixer\DefinedListFixer;
use App\Utils\Data\Fixer\FixerInterface;
use App\Utils\Data\Fixer\FreeListFixer;
use App\Utils\Data\Fixer\IntroFixer;
use App\Utils\Data\Fixer\LanguagesFixer;
use App\Utils\Data\Fixer\NoopFixer;
use App\Utils\Data\Fixer\SinceFixer;
use App\Utils\Data\Fixer\SpeciesListFixer;
use App\Utils\Data\Fixer\StateFixer;
use App\Utils\Data\Fixer\StringFixer;
use App\Utils\Data\Fixer\UrlFixer;

class Fixer
{
    public function __construct(
        private StringFixer $stringFixer,
        private DefinedListFixer $definedListFixer,
        private FreeListFixer $freeListFixer,
        private SpeciesListFixer $speciesListFixer,
        private UrlFixer $urlFixer,
        private ContactAllowedFixer $contactAllowedFixer,
        private CountryFixer $countryFixer,
        private LanguagesFixer $languagesFixer,
        private SinceFixer $sinceFixer,
        private NoopFixer $noopFixer,
        private IntroFixer $introFixer,
        private StateFixer $stateFixer,
    ) {
    }

    public function fix(Artisan $artisan, Field $field): void
    {
        $value = $artisan->get($field);

        if (is_string($value)) {
            $artisan->set($field, $this->getFixer($field)->fix($field->name, $value));
        }
    }

    private function getFixer(Field $field): FixerInterface
    {
        /* @noinspection PhpSwitchCanBeReplacedWithMatchExpressionInspection */
        switch ($field) {
            case Field::NAME:
            case Field::FORMERLY:
            case Field::CITY:
            case Field::PAYMENT_PLANS:
            case Field::NOTES:
                return $this->stringFixer;

            case Field::SPECIES_DOES:
            case Field::SPECIES_DOESNT:
                return $this->speciesListFixer;

            case Field::PRODUCTION_MODELS:
            case Field::FEATURES:
            case Field::STYLES:
            case Field::ORDER_TYPES:
                return $this->definedListFixer;

            case Field::FORMER_MAKER_IDS:
            case Field::OTHER_FEATURES:
            case Field::OTHER_ORDER_TYPES:
            case Field::OTHER_STYLES:
            case Field::URL_PHOTOS:
            case Field::URL_MINIATURES:
            case Field::CURRENCIES_ACCEPTED:
            case Field::PAYMENT_METHODS:
                return $this->freeListFixer;

            case Field::URL_COMMISSIONS:
            case Field::URL_DEVIANTART:
            case Field::URL_FACEBOOK:
            case Field::URL_FAQ:
            case Field::URL_FUR_AFFINITY:
            case Field::URL_FURSUITREVIEW:
            case Field::URL_INSTAGRAM:
            case Field::URL_PRICES:
            case Field::URL_TUMBLR:
            case Field::URL_TWITTER:
            case Field::URL_YOUTUBE:
            case Field::URL_WEBSITE:
            case Field::URL_QUEUE:
            case Field::URL_ETSY:
            case Field::URL_FURTRACK:
                return $this->urlFixer;

            case Field::SINCE:
                return $this->sinceFixer;

            case Field::COUNTRY:
                return $this->countryFixer;

            case Field::STATE:
                return $this->stateFixer;

            case Field::INTRO:
                return $this->introFixer;

            case Field::LANGUAGES:
                return $this->languagesFixer;

            case Field::CONTACT_ALLOWED:
                return $this->contactAllowedFixer;

            default:
                return $this->noopFixer;
        }
    }
}
