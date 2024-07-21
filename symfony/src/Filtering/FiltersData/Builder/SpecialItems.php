<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData\Builder;

use App\Filtering\DataRequests\Consts;
use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;

final class SpecialItems
{
    use UtilityClass;

    public static function faIconFromValue(string $value): string
    {
        return match ($value) {
            Consts::FILTER_VALUE_UNKNOWN => 'fas fa-question-circle',
            Consts::FILTER_VALUE_OTHER => 'fas fa-asterisk',
            default => throw new InvalidArgumentException("Unknown type of value: '$value'"),
        };
    }

    public static function newUnknown(int $initialValue = 0): MutableSpecialItem
    {
        return new MutableSpecialItem(Consts::FILTER_VALUE_UNKNOWN, 'Unknown',
            self::faIconFromValue(Consts::FILTER_VALUE_UNKNOWN), $initialValue);
    }

    public static function newOther(int $initialValue = 0): MutableSpecialItem
    {
        return new MutableSpecialItem(Consts::FILTER_VALUE_OTHER, 'Other',
            self::faIconFromValue(Consts::FILTER_VALUE_OTHER), $initialValue);
    }

    public static function newTrackingIssues(int $initialValue): MutableSpecialItem
    {
        $result = new MutableSpecialItem(Consts::FILTER_VALUE_TRACKING_ISSUES, 'Tracking issues', 'fa fa-exclamation-triangle');
        $result->incCount($initialValue);

        return $result;
    }

    public static function newNotTracked(int $initialValue): MutableSpecialItem
    {
        $result = new MutableSpecialItem(Consts::FILTER_VALUE_NOT_TRACKED, 'Not tracked', 'fas fa-question-circle');
        $result->incCount($initialValue);

        return $result;
    }

    public static function newInactive(int $initialValue): MutableSpecialItem
    {
        $result = new MutableSpecialItem(Consts::FILTER_VALUE_INCLUDE_INACTIVE, 'Show', 'fa fa-play-pause');
        $result->incCount($initialValue);

        return $result;
    }
}
