<?php

declare(strict_types=1);

namespace App\Utils\Filters;

use App\Filtering\Consts;
use App\Utils\Traits\UtilityClass;

final class SpecialItems
{
    use UtilityClass;

    public static function newUnknown(int $initialValue = 0): SpecialItem
    {
        $result = new SpecialItem('_u', Consts::FILTER_VALUE_UNKNOWN, 'Unknown', 'fas fa-question-circle');
        $result->incCount($initialValue);

        return $result;
    }

    public static function newOther(): SpecialItem
    {
        return new SpecialItem('_o', Consts::FILTER_VALUE_OTHER, 'Other', 'fas fa-asterisk');
    }

    public static function newTrackingIssues(int $initialValue): SpecialItem
    {
        $result = new SpecialItem('_ti', Consts::FILTER_VALUE_TRACKING_ISSUES, 'Tracking issues', 'fa fa-exclamation-triangle');
        $result->incCount($initialValue);

        return $result;
    }

    public static function newNotTracked(int $initialValue): SpecialItem
    {
        $result = new SpecialItem('_nt', Consts::FILTER_VALUE_NOT_TRACKED, 'Not tracked', 'fas fa-question-circle');
        $result->incCount($initialValue);

        return $result;
    }
}
