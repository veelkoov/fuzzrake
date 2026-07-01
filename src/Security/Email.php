<?php

declare(strict_types=1);

namespace App\Security;

use App\Utils\HasEmailGetter;
use App\Utils\Traits\UtilityClass;
use Composer\Pcre\Preg;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Veelkoov\Debris\Vecs\StringVec;

final class Email
{
    use UtilityClass;

    // Pattern taken from the Symfony's EmailValidator
    // @author Bernhard Schussek <bschussek@gmail.com>
    private const string PATTERN_HTML5_ALLOW_NO_TLD = '~^[a-zA-Z0-9.!#$%&\'*+\\/=?^_`{|}\~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$~';

    public static function obfuscate(string|HasEmailGetter $input): string
    {
        if ($input instanceof HasEmailGetter) {
            $input = $input->getEmail();
        }

        return StringVec::split('@', $input)
            ->map(function (string $input): string {
                $len = mb_strlen($input);

                if ($len <= 1) {
                    return $input;
                } elseif (2 === $len) {
                    return mb_substr($input, 0, 1).'*';
                } else {
                    return mb_substr($input, 0, 1).str_repeat('*', $len - 2).mb_substr($input, -1);
                }
            })
            ->join('@');
    }

    public static function isValid(string $email): bool
    {
        return Preg::isMatch(self::PATTERN_HTML5_ALLOW_NO_TLD, $email);
    }

    /**
     * @return list<Constraint>
     */
    public static function getConstraints(): array // grep-code-email-constraints
    {
        return [
            new Constraints\NotBlank(message: 'Please enter your email.'),
            new Constraints\Length(max: 256), // If your email address is longer than 256, you already have more important problems
            new Constraints\Email(),
        ];
    }
}
