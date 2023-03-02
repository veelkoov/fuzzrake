<?php

declare(strict_types=1);

namespace App\DataDefinitions\Fields;

use App\Utils\Traits\UtilityClass;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use TypeError;

final class FieldsData
{
    use UtilityClass;

    /**
     * @var array<string, FieldData>
     */
    private static array $fields = [];

    public static function init(): void
    {
        self::$fields = [];

        foreach ((new ReflectionEnum(Field::class))->getCases() as $case) {
            if ($case::class !== ReflectionEnumBackedCase::class) {
                throw new TypeError('I expected backed enum, and what is this?');
            }
            
            foreach ($case->getAttributes() as $attribute) {
                /** @var Properties $data */
                $data = $attribute->newInstance();

                self::$fields[$case->name] = new FieldData(
                    $case->name,
                    $data->modelName,
                    $data->validationRegex,
                    $data->isList,
                    $data->freeForm,
                    $data->inStats,
                    $data->public,
                    $data->inIuForm,
                    $data->date,
                    $data->dynamic,
                    $data->affectedByIuForm,
                    $data->notInspectedUrl,
                );
            }
        }
    }

    public static function get(Field $field): FieldData
    {
        return self::$fields[$field->name];
    }
}

FieldsData::init();
