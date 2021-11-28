<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\DataDefinitions\Fields\Field as F;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Traits\UtilityClass;

final class CompletenessCalc
{
    use UtilityClass;

    private const CRUCIAL = 20;
    private const IMPORTANT = 10;
    private const AVERAGE = 5;
    private const MINOR = 2;
    private const TRIVIAL = 1;
    private const INSIGNIFICANT = 0;

    public static function count(Artisan $artisan): int
    {
        $stateWeight = in_array($artisan->getCountry(), ['US', 'CA']) ? self::AVERAGE : self::INSIGNIFICANT;

        $websites = [
            F::URL_WEBSITE,
            F::URL_DEVIANTART,
            F::URL_FUR_AFFINITY,
            F::URL_TWITTER,
            F::URL_FACEBOOK,
            F::URL_TUMBLR,
            F::URL_INSTAGRAM,
            F::URL_YOUTUBE,
            F::URL_FURRY_AMINO,
        ];

        return (new CompletenessResult($artisan))
            ->anyNotEmpty(self::CRUCIAL, F::MAKER_ID)
            ->anyNotEmpty(self::TRIVIAL, F::INTRO)
            ->anyNotEmpty(self::AVERAGE, F::SINCE)
            ->anyNotEmpty(self::CRUCIAL, F::COUNTRY)
            ->anyNotEmpty($stateWeight, F::STATE)
            ->anyNotEmpty(self::AVERAGE, F::CITY)
            ->anyNotEmpty(self::IMPORTANT, F::PRODUCTION_MODELS)
            ->anyNotEmpty(self::CRUCIAL, F::STYLES, F::OTHER_STYLES)
            ->anyNotEmpty(self::CRUCIAL, F::ORDER_TYPES, F::OTHER_ORDER_TYPES)
            ->anyNotEmpty(self::CRUCIAL, F::FEATURES, F::OTHER_FEATURES)
            ->anyNotEmpty(self::AVERAGE, F::PAYMENT_PLANS)
            ->anyNotEmpty(self::MINOR, F::SPECIES_DOES, F::SPECIES_DOESNT)
            ->anyNotEmpty(self::MINOR, F::URL_PRICES)
            ->anyNotEmpty(self::TRIVIAL, F::URL_FAQ)
            ->anyNotEmpty(self::CRUCIAL, ...$websites)
            ->anyNotEmpty(self::TRIVIAL, F::URL_QUEUE)
            ->anyNotEmpty(self::MINOR, F::LANGUAGES)
            ->anyNotEmpty(self::IMPORTANT, F::URL_MINIATURES)
            ->anyNotEmpty(self::IMPORTANT, F::OPEN_FOR, F::CLOSED_FOR)
            ->anyNotNull(self::CRUCIAL, F::WORKS_WITH_MINORS)
            ->anyNotNull(self::CRUCIAL, F::AGES)
            ->result();
    }
}
