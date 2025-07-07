<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Utils\Traits\UtilityClass;

final class Consts
{
    use UtilityClass;

    public const string DATA_VALUE_UNKNOWN = '';
    public const string FILTER_VALUE_UNKNOWN = '?';
    public const string FILTER_VALUE_OTHER = '*';
    public const string FILTER_VALUE_TRACKING_ISSUES = '!';
    public const string FILTER_VALUE_NOT_TRACKED = '-';
    public const string FILTER_VALUE_INCLUDE_INACTIVE = '.';

    // FIXME: All below around payment plans https://github.com/veelkoov/fuzzrake/issues/305
    public const string DATA_PAYPLANS_NONE = 'None'; // grep-payment-plans-none

    public const string FILTER_LABEL_PAYPLANS_NONE = 'Not supported';
    public const string FILTER_LABEL_PAYPLANS_SUPPORTED = 'Supported';

    public const string FILTER_VALUE_PAYPLANS_SUPPORTED = self::FILTER_LABEL_PAYPLANS_SUPPORTED;
    public const string FILTER_VALUE_PAYPLANS_NONE = self::FILTER_LABEL_PAYPLANS_NONE;
}
