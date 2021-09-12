<?php

declare(strict_types=1);

namespace App\DataDefinitions;

class ContactPermit extends Dictionary
{
    public const NO = 'NO'; // grep-no-contact-allowed
    public const CORRECTIONS = 'CORRECTIONS';
    public const ANNOUNCEMENTS = 'ANNOUNCEMENTS';
    public const FEEDBACK = 'FEEDBACK';

    public static function getValues(): array
    {
        return [
            self::NO            => 'No contact allowed',
            self::CORRECTIONS   => 'Corrections',
            self::ANNOUNCEMENTS => 'Announcements',
            self::FEEDBACK      => 'Feedback',
        ];
    }
}
