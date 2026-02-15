<?php

declare(strict_types=1);

namespace App\Filtering;

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

    public const string FILTER_LABEL_PAYPLANS_NONE = 'Not available';
    public const string FILTER_LABEL_PAYPLANS_SUPPORTED = 'Available';

    public const string FILTER_VALUE_PAYPLANS_SUPPORTED = self::FILTER_LABEL_PAYPLANS_SUPPORTED;
    public const string FILTER_VALUE_PAYPLANS_NONE = self::FILTER_LABEL_PAYPLANS_NONE;
}
