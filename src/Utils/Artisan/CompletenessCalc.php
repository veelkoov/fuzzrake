<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\DataDefinitions\Fields\Field as F;
use App\DataDefinitions\ProductionModels;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringList;
use App\Utils\Traits\UtilityClass;

final class CompletenessCalc
{
    use UtilityClass;

    private const CRUCIAL = 20;
    private const IMPORTANT = 15;
    private const AVERAGE = 10;
    private const MINOR = 5;
    private const TRIVIAL = 2;
    private const INSIGNIFICANT = 0;

    public static function count(Artisan $artisan): int
    {
        /**
         * Ignored (virtual, not available for makers, etc.):
         * SAFE_DOES_NSFW, SAFE_WORKS_WITH_MINORS, URL_MINIATURES, INACTIVE_REASON, CS_LAST_CHECK, CS_TRACKER_ISSUE,
         * BP_LAST_CHECK, BP_TRACKER_ISSUE, COMPLETENESS.
         *
         * Process stuff, insignificant for visitors:
         * PASSWORD, CONTACT_ALLOWED, CONTACT_METHOD, CONTACT_ADDRESS_PLAIN, CONTACT_INFO_OBFUSCATED,
         * CONTACT_INFO_ORIGINAL, NOTES.
         */
        $websites = [
            F::URL_WEBSITE,
            F::URL_FUR_AFFINITY,
            F::URL_DEVIANTART,
            F::URL_TWITTER,
            F::URL_FACEBOOK,
            F::URL_TUMBLR,
            F::URL_INSTAGRAM,
            F::URL_YOUTUBE,
            F::URL_LINKLIST,
            F::URL_FURRY_AMINO,
        ];

        $result = new CompletenessResult($artisan);

        $result
            // Required field, lack of maker ID = very old or imported.
            ->anyNotEmpty(self::CRUCIAL, F::MAKER_ID)

            // Absolutely optional.
            ->anyNotEmpty(self::INSIGNIFICANT, F::FORMER_MAKER_IDS)

            // I wish SO MUCH this field was unused.
            ->anyNotEmpty(self::INSIGNIFICANT, F::FORMERLY)

            // It's not even kinda possible.
            ->anyNotEmpty(self::INSIGNIFICANT, F::NAME)

            // Required field. One of the most important aspect of this website.
            ->anyNotEmpty(self::CRUCIAL, F::COUNTRY)

            // Required fields.
            ->anyNotNull(self::CRUCIAL, F::AGES)
            ->anyNotNull(self::CRUCIAL, F::NSFW_WEBSITE)
            ->anyNotNull(self::CRUCIAL, F::NSFW_SOCIAL)
            ->anyNotEmpty(self::CRUCIAL, ...$websites)

            // We can't force one to receive a review.
            ->anyNotEmpty(self::INSIGNIFICANT, F::URL_FURSUITREVIEW)

            // Nice addition, however can't be expected even from pre-mades-makers.
            ->anyNotEmpty(self::MINOR, F::URL_ETSY, F::URL_THE_DEALERS_DEN, F::URL_OTHER_SHOP)

            // Functional, development field.
            ->anyNotEmpty(self::INSIGNIFICANT, F::URL_OTHER)

            // Deprecated field - not counting.
            ->anyNotEmpty(self::INSIGNIFICANT, F::IS_MINOR)

            // Intro is a nice addition, but the simpler the card, the better.
            ->anyNotEmpty(self::INSIGNIFICANT, F::INTRO)

            // Experience is somewhat important.
            ->anyNotEmpty(self::AVERAGE, F::SINCE)

            // Some do not want to disclose city, not forcing anything.
            ->anyNotEmpty(self::INSIGNIFICANT, F::CITY)

            // Models and styles are kinda important, types and features less than those.
            ->anyNotEmpty(self::IMPORTANT, F::PRODUCTION_MODELS)
            ->anyNotEmpty(self::IMPORTANT, F::STYLES, F::OTHER_STYLES)
            ->anyNotEmpty(self::AVERAGE, F::ORDER_TYPES, F::OTHER_ORDER_TYPES)
            ->anyNotEmpty(self::AVERAGE, F::FEATURES, F::OTHER_FEATURES)

            // Feel free to use them, but the simpler your card is, the better.
            ->anyNotEmpty(self::INSIGNIFICANT, F::PRODUCTION_MODELS_COMMENT, F::STYLES_COMMENT, F::ORDER_TYPES_COMMENT, F::FEATURES_COMMENT)

            // Not so important; plans more than methods, currencies are just extra.
            ->anyNotEmpty(self::AVERAGE, F::PAYMENT_PLANS)
            ->anyNotEmpty(self::MINOR, F::PAYMENT_METHODS)
            ->anyNotEmpty(self::TRIVIAL, F::CURRENCIES_ACCEPTED)

            // People were much interested in species.
            ->anyNotEmpty(self::IMPORTANT, F::SPECIES_DOES, F::SPECIES_DOESNT)

            // But the comment - "the simpler, the better".
            ->anyNotEmpty(self::INSIGNIFICANT, F::SPECIES_COMMENT)

            // Can be helpful at saving some time.
            ->anyNotEmpty(self::TRIVIAL, F::URL_FAQ)

            // It's unusual for non-English speaking to show.
            ->anyNotEmpty(self::MINOR, F::LANGUAGES)

            // Extra points for photos.
            ->anyNotEmpty(self::IMPORTANT, F::URL_PHOTOS)

            // A bit less extra points for those. You can upload at least one photo yourself.
            ->anyNotEmpty(self::AVERAGE, F::URL_SCRITCH, F::URL_FURTRACK)

            // Fuzzrake may fail detecting statuses. Assume fail is on our side.
            ->anyNotEmpty(self::INSIGNIFICANT, F::OPEN_FOR, F::CLOSED_FOR)
        ;

        if (in_array(ProductionModels::STANDARD_COMMISSIONS, StringList::unpack($artisan->getProductionModels()))) {
            // If you do commissions, these make sense.
            $result
                ->anyNotEmpty(self::AVERAGE, F::URL_COMMISSIONS)
                ->anyNotEmpty(self::MINOR, F::URL_PRICES)
                ->anyNotEmpty(self::TRIVIAL, F::URL_QUEUE)
            ;
        }

        if (in_array($artisan->getCountry(), ['US', 'CA'])) {
            // Optional for non-US and non-CA. Not as important as country, because it's the same country.
            $result->anyNotEmpty(self::MINOR, F::STATE);
        }

        if ($artisan->isAllowedToWorkWithMinors()) {
            // Required field.
            $result->anyNotNull(self::CRUCIAL, F::WORKS_WITH_MINORS);
        }

        if ($artisan->isAllowedToDoNsfw()) {
            // Required field.
            $result->anyNotNull(self::CRUCIAL, F::DOES_NSFW);
        }

        return $result->result();
    }
}
