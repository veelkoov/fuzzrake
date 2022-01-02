<?php

declare(strict_types=1);

namespace App\DataDefinitions;

class ContactPermit extends Dictionary
{
    final public const NO = 'NO'; // grep-no-contact-allowed
    final public const CORRECTIONS = 'CORRECTIONS';
    final public const ANNOUNCEMENTS = 'ANNOUNCEMENTS';
    final public const FEEDBACK = 'FEEDBACK';

    public static function getValues(): array
    {
        return [
            self::NO            => 'Never',
            self::CORRECTIONS   => 'Corrections',
            self::ANNOUNCEMENTS => 'Announcements',
            self::FEEDBACK      => 'Feedback',
        ];
    }
}
