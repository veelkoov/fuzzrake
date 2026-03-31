<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use App\Utils\Traits\UtilityClass;

final class ValidationGroups
{
    use UtilityClass;

    public const string ENFORCE_USER = 'validation_group_enforce_user';
}
