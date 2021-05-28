<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;
use App\Utils\Traits\UtilityClass;

final class Password
{
    use UtilityClass;

    public static function encryptOn(Artisan $artisan): void
    {
        $artisan->setPassword(password_hash(
            $artisan->getPassword(),
            PASSWORD_DEFAULT,
            ['cost' => 12],
        ));
    }
}
