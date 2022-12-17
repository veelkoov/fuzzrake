<?php

declare(strict_types=1);

namespace App\Filters;

use App\Utils\Traits\UtilityClass;

final class Consts
{
    use UtilityClass;

    public const DATA_VALUE_UNKNOWN = '';
    public const FILTER_VALUE_UNKNOWN = '?'; // grep-special-value-unknown

    public const DATA_PAYPLANS_NONE = 'None'; // grep-payment-plans-none

    public const FILTER_LABEL_PAYPLANS_NONE = 'Not supported';
    public const FILTER_LABEL_PAYPLANS_SUPPORTED = 'Supported';

    public const FILTER_VALUE_PAYPLANS_SUPPORTED = self::FILTER_LABEL_PAYPLANS_SUPPORTED;
    public const FILTER_VALUE_PAYPLANS_NONE = self::FILTER_LABEL_PAYPLANS_NONE;
}
