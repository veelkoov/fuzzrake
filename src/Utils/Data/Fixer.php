<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\DataDefinitions\Fields\Field as F;
use App\DataDefinitions\Fields\Fields;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\Fixer\CountryFixer;
use App\Utils\Data\Fixer\CurrencyFixer;
use App\Utils\Data\Fixer\DefinedListFixer;
use App\Utils\Data\Fixer\FixerInterface;
use App\Utils\Data\Fixer\FreeListFixer;
use App\Utils\Data\Fixer\LanguagesFixer;
use App\Utils\Data\Fixer\NoopFixer;
use App\Utils\Data\Fixer\PayMethodFixer;
use App\Utils\Data\Fixer\PayPlanFixer;
use App\Utils\Data\Fixer\SinceFixer;
use App\Utils\Data\Fixer\SpeciesListFixer;
use App\Utils\Data\Fixer\StateFixer;
use App\Utils\Data\Fixer\StringFixer;
use App\Utils\Data\Fixer\UrlFixer;
use App\Utils\Data\Fixer\UrlListFixer;

class Fixer
{
    public function __construct(
        private readonly StringFixer $stringFixer,
        private readonly DefinedListFixer $definedListFixer,
        private readonly FreeListFixer $freeListFixer,
        private readonly SpeciesListFixer $speciesListFixer,
        private readonly UrlFixer $urlFixer,
        private readonly UrlListFixer $urlListFixer,
        private readonly CountryFixer $countryFixer,
        private readonly LanguagesFixer $languagesFixer,
        private readonly SinceFixer $sinceFixer,
        private readonly NoopFixer $noopFixer,
        private readonly StateFixer $stateFixer,
        private readonly PayPlanFixer $payPlanFixer,
        private readonly CurrencyFixer $currencyFixer,
        private readonly PayMethodFixer $payMethodFixer,
    ) {
    }

    public function getFixed(Artisan $input): Artisan
    {
        $result = clone $input;

        foreach (Fields::persisted() as $field) {
            $this->fix($result, $field);
        }

        return $result;
    }

    public function fix(Artisan $artisan, F $field): void
    {
        $value = $artisan->get($field);

        if (is_string($value)) {
            $artisan->set($field, $this->getFixer($field)->fix($value));
        }
    }

    private function getFixer(F $field): FixerInterface
    {
        return match ($field) {
            F::NAME, F::FORMERLY, F::CITY, F::NOTES => $this->stringFixer,

            F::SPECIES_DOES, F::SPECIES_DOESNT => $this->speciesListFixer,

            F::PRODUCTION_MODELS, F::FEATURES, F::STYLES, F::ORDER_TYPES => $this->definedListFixer,

            F::FORMER_MAKER_IDS, F::OTHER_FEATURES, F::OTHER_ORDER_TYPES, F::OTHER_STYLES, F::URL_MINIATURES, => $this->freeListFixer,

            F::URL_COMMISSIONS, F::URL_DEVIANTART, F::URL_FACEBOOK, F::URL_FAQ, F::URL_FUR_AFFINITY, F::URL_FURSUITREVIEW, F::URL_INSTAGRAM, F::URL_PRICES, F::URL_TUMBLR, F::URL_TWITTER, F::URL_YOUTUBE, F::URL_WEBSITE, F::URL_QUEUE, F::URL_ETSY, F::URL_FURTRACK, F::URL_LINKLIST, F::URL_PHOTOS => ($field->isList() ? $this->urlListFixer : $this->urlFixer),

            F::SINCE               => $this->sinceFixer,
            F::COUNTRY             => $this->countryFixer,
            F::STATE               => $this->stateFixer,
            F::LANGUAGES           => $this->languagesFixer,
            F::PAYMENT_PLANS       => $this->payPlanFixer,
            F::PAYMENT_METHODS     => $this->payMethodFixer,
            F::CURRENCIES_ACCEPTED => $this->currencyFixer,

            default => $this->noopFixer,
        };
    }
}
