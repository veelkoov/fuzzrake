<?php

declare(strict_types=1);

namespace App\ValueObject;

use App\Utils\Traits\UtilityClass;

final class Texts
{
    use UtilityClass;

    public const string UPDATES_PASSWORD = 'Updates password'; // grep-text-updates-password
    public const string WANT_TO_CHANGE_PASSWORD = 'I want to change my password / I forgot my password';
}
