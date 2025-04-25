<?php

declare(strict_types=1);

namespace App\Data\Fixer;

use App\Data\Definitions\Fields\Field as F;
use App\Data\Definitions\Fields\Fields;
use App\Data\Fixer\String\CountryFixer;
use App\Data\Fixer\String\GenericStringFixer;
use App\Data\Fixer\String\NoopStringFixer;
use App\Data\Fixer\String\SinceStringFixer;
use App\Data\Fixer\String\StateFixerConfigurable;
use App\Data\Fixer\String\UrlFixerGeneric;
use App\Data\Fixer\StrList\CurrencyFixer;
use App\Data\Fixer\StrList\DefinedListFixer;
use App\Data\Fixer\StrList\FreeListFixer;
use App\Data\Fixer\StrList\LanguagesFixer;
use App\Data\Fixer\StrList\NoopStrListFixer;
use App\Data\Fixer\StrList\PayMethodFixer;
use App\Data\Fixer\StrList\PayPlanFixer;
use App\Data\Fixer\StrList\SpeciesListFixer;
use App\Data\Fixer\StrList\UrlListStringFixer;
use App\Utils\Creator\SmartAccessDecorator as Creator;

class Fixer
{
    public function __construct(
        private readonly GenericStringFixer $stringFixer,
        private readonly DefinedListFixer $definedListFixer,
        private readonly FreeListFixer $freeListFixer,
        private readonly SpeciesListFixer $speciesListFixer,
        private readonly UrlFixerGeneric $urlFixer,
        private readonly UrlListStringFixer $urlListFixer,
        private readonly CountryFixer $countryFixer,
        private readonly LanguagesFixer $languagesFixer,
        private readonly SinceStringFixer $sinceFixer,
        private readonly NoopStringFixer $noopFixer,
        private readonly NoopStrListFixer $noopListFixer,
        private readonly StateFixerConfigurable $stateFixer,
        private readonly PayPlanFixer $payPlanFixer,
        private readonly CurrencyFixer $currencyFixer,
        private readonly PayMethodFixer $payMethodFixer,
    ) {
    }

    public function getFixed(Creator $input): Creator
    {
        $result = clone $input;

        foreach (Fields::persisted() as $field) {
            $this->fix($result, $field);
        }

        return $result;
    }

    public function fix(Creator $creator, F $field): void
    {
        if ($field->isList()) {
            $value = $creator->getStringList($field);

            $creator->set($field, $this->getStrListFixer($field)->fix($value));
        } else {
            $value = $creator->get($field);

            if (is_string($value)) {
                $creator->set($field, $this->getFixer($field)->fix($value));
            }
        }
    }

    private function getFixer(F $field): StringFixerInterface
    {
        return match ($field) {
            F::NAME, F::CITY => $this->stringFixer,

            F::URL_DEVIANTART, F::URL_FACEBOOK, F::URL_FAQ, F::URL_FUR_AFFINITY, F::URL_FURSUITREVIEW, F::URL_INSTAGRAM, F::URL_TUMBLR, F::URL_TWITTER, F::URL_YOUTUBE, F::URL_WEBSITE, F::URL_QUEUE, F::URL_ETSY, F::URL_FURTRACK, F::URL_LINKLIST => $this->urlFixer,

            F::SINCE               => $this->sinceFixer,
            F::COUNTRY             => $this->countryFixer,
            F::STATE               => $this->stateFixer,

            default => $this->noopFixer,
        };
    }

    private function getStrListFixer(F $field): StrListFixerInterface
    {
        return match ($field) {
            F::SPECIES_DOES, F::SPECIES_DOESNT => $this->speciesListFixer,

            F::PRODUCTION_MODELS, F::FEATURES, F::STYLES, F::ORDER_TYPES => $this->definedListFixer,

            F::FORMERLY, F::OTHER_FEATURES, F::OTHER_ORDER_TYPES, F::OTHER_STYLES => $this->freeListFixer,

            F::URL_COMMISSIONS, F::URL_PRICES, F::URL_PHOTOS => $this->urlListFixer,

            F::LANGUAGES           => $this->languagesFixer,
            F::PAYMENT_PLANS       => $this->payPlanFixer,
            F::PAYMENT_METHODS     => $this->payMethodFixer,
            F::CURRENCIES_ACCEPTED => $this->currencyFixer,

            default => $this->noopListFixer,
        };
    }
}
