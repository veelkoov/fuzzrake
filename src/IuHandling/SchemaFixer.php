<?php

declare(strict_types=1);

namespace App\IuHandling;

use App\Data\Definitions\Fields\Field;
use App\Utils\Enforce;
use App\Utils\PackedStringList;
use App\Utils\Traits\UtilityClass;

final class SchemaFixer
{
    use UtilityClass;

    private const string SCHEMA_VERSION = 'SCHEMA_VERSION';
    private const int CURRENT_SCHEMA_VERSION = 20;

    /**
     * @param psJsonFieldsData $data
     *
     * @return psJsonFieldsData
     */
    public static function fix(array $data): array
    {
        switch ($data[self::SCHEMA_VERSION]) {
            case 13:
                unset($data['BP_LAST_CHECK']);

                if (!array_key_exists(Field::URL_MASTODON->value, $data)) {
                    $data[Field::URL_MASTODON->value] = '';
                }
                // no break

            case 14:
                $data[Field::URL_OTHER->value] = PackedStringList::unpack(Enforce::string($data[Field::URL_OTHER->value]));
                // no break

            case 15:
                unset($data['IS_MINOR']);
                // no break

            case 16:
                unset($data['CONTACT_METHOD']);
                unset($data['CONTACT_ADDRESS_PLAIN']);
                $data['EMAIL_ADDRESS_OBFUSCATED'] = $data['CONTACT_INFO_OBFUSCATED'];
                $data['EMAIL_ADDRESS'] = $data['CONTACT_INFO_ORIGINAL'];
                unset($data['CONTACT_INFO_OBFUSCATED']);
                unset($data['CONTACT_INFO_ORIGINAL']);
                // no break

            case 17:
                $data[Field::URL_BLUESKY->value] = '';
                $data[Field::URL_DONATIONS->value] = '';
                $data[Field::URL_TELEGRAM_CHANNEL->value] = '';
                $data[Field::URL_TIKTOK->value] = '';
                // no break

            case 18:
                $paymentPlans = implode("\n", Enforce::strList($data['PAYMENT_PLANS']));
                // Better handle all cases manually
                $data[Field::PAYMENT_PLANS_INFO->value] = '' === $paymentPlans ? '' : '(set '.Field::OFFERS_PAYMENT_PLANS->value." 'True') $paymentPlans";
                $data[Field::OFFERS_PAYMENT_PLANS->value] = null; // See above

                $data[Field::HAS_ALLERGY_WARNING->value] = null;
                $data[Field::ALLERGY_WARNING_INFO->value] = '';
                // no break

            case 19:
                $data[Field::OFFERS_COMMENT->value] = $data['PRODUCTION_MODELS_COMMENT'];
                $data[Field::OFFERS->value] = $data['PRODUCTION_MODELS'];
                unset($data['PRODUCTION_MODELS_COMMENT']);
                unset($data['PRODUCTION_MODELS']);

                $data[Field::PRODUCTS_COMMENT->value] = $data['ORDER_TYPES_COMMENT'];
                $data[Field::OTHER_PRODUCTS->value] = $data['OTHER_ORDER_TYPES'];
                $data[Field::PRODUCTS->value] = $data['ORDER_TYPES'];
                unset($data['ORDER_TYPES_COMMENT']);
                unset($data['OTHER_ORDER_TYPES']);
                $data[Field::PRODUCTS->value] = $data['ORDER_TYPES'];
                unset($data['ORDER_TYPES']);

                $data[Field::PRICES_IN->value] = $data['CURRENCIES_ACCEPTED'];
                unset($data['CURRENCIES_ACCEPTED']);
        }

        return $data;
    }

    /**
     * @param psJsonFieldsData $data
     *
     * @return psJsonFieldsData
     */
    public static function appendSchemaVersion(array $data): array
    {
        $data[self::SCHEMA_VERSION] = self::CURRENT_SCHEMA_VERSION;

        return $data;
    }
}
