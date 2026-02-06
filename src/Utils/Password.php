<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;

final class Password
{
    use UtilityClass;

    public static function encryptOn(Creator $creator): void
    {
        // grep-password-algorithms
        $creator->setPassword(password_hash(
            $creator->getPassword(),
            PASSWORD_DEFAULT,
            ['cost' => 12],
        ));
    }

    public static function verify(Creator $creator, string $hash): bool
    {
        return strlen($hash) > 0 && password_verify($creator->getPassword(), $hash);
    }
}
