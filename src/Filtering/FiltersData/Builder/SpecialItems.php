<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData\Builder;

use App\Filtering\DataRequests\Consts;
use App\Utils\Traits\UtilityClass;

final class SpecialItems
{
    use UtilityClass;

    public static function newUnknown(int $initialValue = 0): MutableSpecialItem
    {
        return new MutableSpecialItem(Consts::FILTER_VALUE_UNKNOWN, 'Unknown',
            'fas fa-question-circle', $initialValue);
    }

    public static function newOther(): MutableSpecialItem
    {
        return new MutableSpecialItem(Consts::FILTER_VALUE_OTHER, 'Other', 'fas fa-asterisk');
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
