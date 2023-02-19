<?php

declare(strict_types=1);

namespace App\Data\Fixer;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\Fields;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class Fixer
{
    public function __construct(
        private readonly StringFixer $stringFixer,
        private readonly DefinedListFixer $definedListFixer,
        private readonly FreeListFixer $freeListFixer,
        private readonly SpeciesListFixer $speciesListFixer,
        private readonly UrlFixer $urlFixer,
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

    public function fix(Artisan $artisan, Field $field): void
    {
        $value = $artisan->get($field);

        if (is_string($value)) {
            $artisan->set($field, $this->getFixer($field)->fix($value));
        }
    }

    private function getFixer(Field $field): FixerInterface
    {
        return match ($field) {
            Field::NAME, Field::FORMERLY, Field::CITY, Field::NOTES => $this->stringFixer,

            Field::SPECIES_DOES, Field::SPECIES_DOESNT => $this->speciesListFixer,

            Field::PRODUCTION_MODELS, Field::FEATURES, Field::STYLES, Field::ORDER_TYPES => $this->definedListFixer,

            Field::FORMER_MAKER_IDS, Field::OTHER_FEATURES, Field::OTHER_ORDER_TYPES, Field::OTHER_STYLES, Field::URL_PHOTOS, Field::URL_MINIATURES, => $this->freeListFixer,

            Field::URL_COMMISSIONS, Field::URL_DEVIANTART, Field::URL_FACEBOOK, Field::URL_FAQ, Field::URL_FUR_AFFINITY, Field::URL_FURSUITREVIEW, Field::URL_INSTAGRAM, Field::URL_PRICES, Field::URL_TUMBLR, Field::URL_TWITTER, Field::URL_YOUTUBE, Field::URL_WEBSITE, Field::URL_QUEUE, Field::URL_ETSY, Field::URL_FURTRACK => $this->urlFixer,

            Field::SINCE               => $this->sinceFixer,
            Field::COUNTRY             => $this->countryFixer,
            Field::STATE               => $this->stateFixer,
            Field::LANGUAGES           => $this->languagesFixer,
            Field::PAYMENT_PLANS       => $this->payPlanFixer,
            Field::PAYMENT_METHODS     => $this->payMethodFixer,
            Field::CURRENCIES_ACCEPTED => $this->currencyFixer,

            default                    => $this->noopFixer,
        };
    }
}
