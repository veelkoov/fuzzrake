<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\Data\Definitions\Fields\Field;
use App\Entity\ArtisanValue as CreatorValue;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;
use Psl\Vec;

final class PartialCreatorValueListAccessor
{
    use UtilityClass;

    /**
     * @return list<string>
     */
    public static function get(Creator $creator, Field $field): array
    {
        return Vec\map(
            self::getObjects($creator, $field),
            fn (CreatorValue $value): string => $value->getValue(),
        );
    }

    /**
     * @return list<CreatorValue>
     */
    private static function getObjects(Creator $creator, Field $field): array
    {
        return Vec\filter(
            $creator->getArtisan()->getValues(),
            fn (CreatorValue $value): bool => $value->getFieldName() === $field->value,
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
            $newValue = (new CreatorValue())->setFieldName($field->value);
            $creator->getArtisan()->addValue($newValue);

            $existing[] = $newValue;
        }

        $theLastIdx = count($values) - 1;

        for ($i = 0; $i <= $theLastIdx; ++$i) {
            $existing[$i]->setValue($values[$i]);
        }

        $firstToRemoveIdx = count($values);
        $lastToRemoveIdx = count($existing) - 1;

        for ($i = $firstToRemoveIdx; $i <= $lastToRemoveIdx; ++$i) {
            $creator->getArtisan()->removeValue($existing[$i]);
        }
    }
}
