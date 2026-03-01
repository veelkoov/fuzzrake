<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use App\Utils\Traits\UtilityClass;

final class Validation // FIXME: https://github.com/veelkoov/fuzzrake/issues/284
{
    use UtilityClass;

    public const string GRP_DATA = 'iu_data';
    public const string GRP_CONTACT_AND_PASSWORD = 'iu_contact_and_password';
}
