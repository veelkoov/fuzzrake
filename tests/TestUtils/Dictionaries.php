<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Utils\Artisan\ContactPermit;
use App\Utils\Artisan\Features;
use App\Utils\Artisan\OrderTypes;
use App\Utils\Artisan\ProductionModels;
use App\Utils\Artisan\Styles;
use Symfony\Component\Yaml\Yaml;

class Dictionaries
{
    private static ?array $attributesData;

    public static function getStyles(): Styles
    {
        return new Styles(self::getAttributesData());
    }

    public static function getOrderTypes(): OrderTypes
    {
        return new OrderTypes(self::getAttributesData());
    }

    public static function getFeatures(): Features
    {
        return new Features(self::getAttributesData());
    }

    public static function getContactPermit(): ContactPermit
    {
        return new ContactPermit(self::getAttributesData());
    }

    public static function getProductionModels(): ProductionModels
    {
        return new ProductionModels(self::getAttributesData());
    }

    private static function getAttributesData(): array
    {
        return self::$attributesData = self::$attributesData ?? Yaml::parseFile(__DIR__.'/../../config/data_definitions/attributes.yaml')['parameters']['attributes'];
    }
}
