<?php

declare(strict_types=1);

namespace App\Data\Definitions;

use App\Utils\Traits\EnumUtils;

enum ContactPermit: string
{
    use EnumUtils;

    case NO = 'NO'; // grep-no-contact-allowed
    case CORRECTIONS = 'CORRECTIONS';
    case ANNOUNCEMENTS = 'ANNOUNCEMENTS';
    case FEEDBACK = 'FEEDBACK';

    public function getLabel(): string
    {
        return match ($this) {
            self::NO            => 'Never',
            self::CORRECTIONS   => 'Corrections',
            self::ANNOUNCEMENTS => 'Announcements',
            self::FEEDBACK      => 'Feedback',
        };
    }

    public static function isAtLeastCorrections(?self $value): bool
    {
        return null != $value && self::NO !== $value;
    }
}
