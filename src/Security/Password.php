<?php

declare(strict_types=1);

namespace App\Security;

use App\Utils\Traits\UtilityClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class Password
{
    use UtilityClass;

    /**
     * @return list<Constraint>
     */
    public static function getConstraints(): array
    {
        return [
            new NotBlank(message: 'Please enter a password'),
            new Length(
                min: 8, // grep-code-min-password-length
                max: 512, // 4096 = max length allowed by Symfony for security reasons, 512 = performance/!DoS
                minMessage: 'Your password must be at least {{ limit }} characters.',
            ),
        ];
    }
}
