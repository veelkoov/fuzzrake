<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use App\Utils\Traits\UtilityClass;

class Validation
{
    use UtilityClass;

    final public const string GRP_DATA = 'iu_data';
    final public const string GRP_CONTACT_AND_PASSWORD = 'iu_contact_and_password';
}
