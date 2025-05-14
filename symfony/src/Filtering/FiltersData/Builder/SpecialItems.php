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
        return new MutableSpecialItem(Consts::FILTER_VALUE_UNKNOWN, 'Unknown', // grep-special-label-unknown
            self::faIconFromValue(Consts::FILTER_VALUE_UNKNOWN), $initialValue);
    }

    public static function newOther(int $initialValue = 0): MutableSpecialItem
    {
        return new MutableSpecialItem(Consts::FILTER_VALUE_OTHER, 'Other', // grep-special-label-other
            self::faIconFromValue(Consts::FILTER_VALUE_OTHER), $initialValue);
    }

    public static function newTrackingIssues(int $initialValue): MutableSpecialItem
    {
        return new MutableSpecialItem(Consts::FILTER_VALUE_TRACKING_ISSUES, 'Tracking issues', // grep-special-label-tracking-issues
            'fa fa-exclamation-triangle', $initialValue);
    }

    public static function newNotTracked(int $initialValue): MutableSpecialItem
    {
        return new MutableSpecialItem(Consts::FILTER_VALUE_NOT_TRACKED, 'Not tracked', // grep-special-label-not-tracked
            'fas fa-question-circle', $initialValue);
    }

    public static function newInactive(int $initialValue): MutableSpecialItem
    {
        return new MutableSpecialItem(Consts::FILTER_VALUE_INCLUDE_INACTIVE, 'Show', // grep-special-label-show-inactive
            'fa fa-play-pause', $initialValue);
    }
}
