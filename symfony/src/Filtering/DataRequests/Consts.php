<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Utils\Traits\UtilityClass;

final class Consts
{
    use UtilityClass;

    public const string DATA_VALUE_UNKNOWN = '';
    public const string FILTER_VALUE_UNKNOWN = '?'; // grep-special-value-unknown
    public const string FILTER_VALUE_OTHER = '*'; // grep-special-value-other
    public const string FILTER_VALUE_TRACKING_ISSUES = '!'; // grep-special-value-tracking-issues
    public const string FILTER_VALUE_NOT_TRACKED = '-'; // grep-special-value-not-tracked
    public const string FILTER_VALUE_INCLUDE_INACTIVE = '.';

    public const string DATA_PAYPLANS_NONE = 'None'; // grep-payment-plans-none

    public const string FILTER_LABEL_PAYPLANS_NONE = 'Not supported';
    public const string FILTER_LABEL_PAYPLANS_SUPPORTED = 'Supported';

    public const string FILTER_VALUE_PAYPLANS_SUPPORTED = self::FILTER_LABEL_PAYPLANS_SUPPORTED;
    public const string FILTER_VALUE_PAYPLANS_NONE = self::FILTER_LABEL_PAYPLANS_NONE;
}
