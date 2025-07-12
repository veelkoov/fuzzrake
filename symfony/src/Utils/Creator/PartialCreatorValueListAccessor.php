<?php

declare(strict_types=1);

namespace App\Utils\Creator;

use App\Data\Definitions\Fields\Field;
use App\Entity\CreatorValue;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;

final class PartialCreatorValueListAccessor
{
    use UtilityClass;

    /**
     * @return list<string>
     */
    public static function get(Creator $creator, Field $field): array
    {
        return arr_map(
            self::getObjects($creator, $field),
            static fn (CreatorValue $value): string => $value->getValue(),
        );
    }

    /**
     * @return list<CreatorValue>
     */
    private static function getObjects(Creator $creator, Field $field): array
    {
        return iter_filterl(
            $creator->entity->getValues(),
            static fn (CreatorValue $value) => $value->getFieldName() === $field->value,
        );
    }

    /**
     * @param list<string> $values
     */
    public static function set(Creator $creator, Field $field, array $values): void
    {
        $existing = self::getObjects($creator, $field);

        $howManyNewToCreate = count($values) - count($existing);

        for ($i = 0; $i < $howManyNewToCreate; ++$i) {
            $newValue = new CreatorValue()->setFieldName($field->value);
            $creator->entity->addValue($newValue);

            $existing[] = $newValue;
        }

        $theLastIdx = count($values) - 1;

        for ($i = 0; $i <= $theLastIdx; ++$i) {
            $existing[$i]->setValue($values[$i]);
        }

        $firstToRemoveIdx = count($values);
        $lastToRemoveIdx = count($existing) - 1;

        for ($i = $firstToRemoveIdx; $i <= $lastToRemoveIdx; ++$i) {
            $creator->entity->removeValue($existing[$i]);
        }
    }
}
