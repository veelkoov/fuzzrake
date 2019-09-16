<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Artisan;
use App\Utils\Artisan\Features;
use App\Utils\Artisan\Fields;
use App\Utils\Artisan\OrderTypes;
use App\Utils\Artisan\ProductionModels;
use App\Utils\Artisan\Styles;
use App\Utils\Regexp\Utils;
use PHPUnit\Framework\TestCase;

/**
 * Don't judge, I'm having a lot of fun here!
 */
class IuFormServiceTest extends TestCase
{
    private const REGEXP_DATA_ITEM_PUSH = '#\s\d+ +=> (?:\$this->transform[a-z]+\()?\$artisan->get(?<name>[a-z]+)\(\)\)?,#i';

    public function testServiceCodeNaively(): void
    {
        $checkedSource = file_get_contents(__DIR__.'/../../src/Service/IuFormService.php');

        static::assertGreaterThan(0, Utils::matchAll(self::REGEXP_DATA_ITEM_PUSH, $checkedSource, $matches));

        $fieldsInForm = Fields::exportedToIuForm();
        unset($fieldsInForm[Fields::VALIDATION_CHECKBOX]);

        foreach ($matches['name'] as $modelName) {
            $field = Fields::getByModelName(lcfirst($modelName));
            $name = $field->is(Fields::CONTACT_INFO_OBFUSCATED) ? Fields::CONTACT_INPUT_VIRTUAL : $field->name();

            static::assertArrayHasKey($name, $fieldsInForm);

            unset($fieldsInForm[$name]);
        }

        static::assertEmpty($fieldsInForm, 'Fields left to be matched: '.join(', ', $fieldsInForm));
    }

    /**
     * @dataProvider formDataPrefillDataProvider
     *
     * @param Artisan $artisan
     */
    public function testFormDataPrefill(Artisan $artisan): void
    {
        // TODO: implement
    }

    public function formDataPrefillDataProvider(): array
    {
        return [
            (new Artisan())
                ->setName('ARTISAN_NAME')
                ->setFormerly('ARTISAN_FORMERLY')
                ->setSince('2019-09')
                ->setCountry('FI')
                ->setState('ARTISAN_STATE')
                ->setCity('ARTISAN_CITY')
                ->setPaymentPlans('ARTISAN_PAYMENT_PLANS')
                ->setPricesUrl('ARTISAN_PRICES_URL')
                ->setProductionModels(ProductionModels::getAllValuesAsString())
                ->setStyles(Styles::getAllValuesAsString())
                ->setOtherStyles('ARTISAN_OTHER_STYLES')
                ->setOrderTypes(OrderTypes::getAllValuesAsString())
                ->setOtherOrderTypes('ARTISAN_OTHER_ORDER_TYPES')
                ->setFeatures(Features::getAllValuesAsString())
                ->setOtherFeatures('ARTISAN_OTHER_FEATURES')
                ->setSpeciesDoes('ARTISAN_SPECIES_DOES')
                ->setSpeciesDoesnt('ARTISAN_SPECIES_DOESNT')
                ->setFursuitReviewUrl('ARTISAN_FURSUITREVIEW_URL')
                ->setWebsiteUrl('ARTISAN_WEBSITE_URL')
                ->setFaqUrl('ARTISAN_FAQ_URL'),
                // TODO: finish
        ];
    }
}
