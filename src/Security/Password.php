<?php

declare(strict_types=1);

namespace App\Security;

use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

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

    /**
     * @return list<Constraint>
     */
    public static function getConstraints(): array
    {
        return [
            new NotBlank(message: 'Please enter a password'),
            new Length(
                min: 8, // grep-password-length
                max: 512, // 4096 = max length allowed by Symfony for security reasons, 512 = performance/!DoS
                minMessage: 'Your password must be at least {{ limit }} characters.',
            ),
        ];
    }
}
