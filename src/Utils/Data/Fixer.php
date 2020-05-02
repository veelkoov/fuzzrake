<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Entity\Artisan;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
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
    private StringFixer $stringFixer;
    private DefinedListFixer $definedListFixer;
    private FreeListFixer $freeListFixer;
    private SpeciesListFixer $speciesListFixer;
    private UrlFixer $urlFixer;
    private ContactAllowedFixer $contactAllowedFixer;
    private CountryFixer $countryFixer;
    private LanguagesFixer $languagesFixer;
    private SinceFixer $sinceFixer;
    private NoopFixer $noopFixer;
    private IntroFixer $introFixer;
    private StateFixer $stateFixer;

    public function __construct(SpeciesListFixer $speciesListFixer, LanguagesFixer $languagesFixer,
        CountryFixer $countryFixer, StateFixer $stateFixer, StringFixer $stringFixer,
        DefinedListFixer $definedListFixer, FreeListFixer $freeListFixer, UrlFixer $urlFixer, IntroFixer $introFixer)
    {
        $this->speciesListFixer = $speciesListFixer;
        $this->languagesFixer = $languagesFixer;

        $this->stringFixer = $stringFixer;
        $this->definedListFixer = $definedListFixer;
        $this->freeListFixer = $freeListFixer;
        $this->urlFixer = $urlFixer;
        $this->noopFixer = new NoopFixer();
        $this->sinceFixer = new SinceFixer();
        $this->countryFixer = $countryFixer;
        $this->stateFixer = $stateFixer;
        $this->introFixer = $introFixer;
        $this->contactAllowedFixer = new ContactAllowedFixer();
    }

    public function fix(Artisan $artisan, Field $field): void
    {
        $artisan->set($field, $this->getFixer($field)->fix($field->name(), $artisan->get($field)));
    }

    private function getFixer(Field $field): FixerInterface
    {
        switch ($field->name()) {
            case Fields::NAME:
            case Fields::CITY:
            case Fields::URL_OTHER:
            case Fields::PAYMENT_PLANS:
            case Fields::NOTES:
                return $this->stringFixer;

            case Fields::SPECIES_DOES:
            case Fields::SPECIES_DOESNT:
                return $this->speciesListFixer;

            case Fields::PRODUCTION_MODELS:
            case Fields::FEATURES:
            case Fields::STYLES:
            case Fields::ORDER_TYPES:
                return $this->definedListFixer;

            case Fields::FORMER_MAKER_IDS:
            case Fields::OTHER_FEATURES:
            case Fields::OTHER_ORDER_TYPES:
            case Fields::OTHER_STYLES:
            case Fields::URL_SCRITCH_PHOTO:
            case Fields::URL_SCRITCH_MINIATURE:
                return $this->freeListFixer;

            case Fields::URL_CST:
            case Fields::URL_DEVIANTART:
            case Fields::URL_FACEBOOK:
            case Fields::URL_FAQ:
            case Fields::URL_FUR_AFFINITY:
            case Fields::URL_FURSUITREVIEW:
            case Fields::URL_INSTAGRAM:
            case Fields::URL_PRICES:
            case Fields::URL_TUMBLR:
            case Fields::URL_TWITTER:
            case Fields::URL_YOUTUBE:
            case Fields::URL_WEBSITE:
            case Fields::URL_QUEUE:
                return $this->urlFixer;

            case Fields::SINCE:
                return $this->sinceFixer;

            case Fields::COUNTRY:
                return $this->countryFixer;

            case Fields::STATE:
                return $this->stateFixer;

            case Fields::INTRO:
                return $this->introFixer;

            case Fields::LANGUAGES:
                return $this->languagesFixer;

            case Fields::CONTACT_ALLOWED:
                return $this->contactAllowedFixer;

            default:
                return $this->noopFixer;
        }
    }
}
