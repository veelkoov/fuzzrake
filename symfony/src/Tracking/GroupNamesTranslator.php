<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Utils\Collections\StringList;
use App\Utils\Traits\UtilityClass;

final class GroupNamesTranslator
{
    use UtilityClass;

    private const array TRANSLATIONS = [
        'Cms' => 'Commissions',
        'HandpawsCms' => 'Handpaws commissions',
        'SockpawsCms' => 'Sockpaws commissions',
        'FullsuitCommissions' => 'Fullsuit commissions',
        'PartialCommissions' => 'Partial commissions',
        'HeadCommissions' => 'Head commissions',
        'ArtisticLiberty' => 'Artistic liberty',
    ];

    public static function toOffers(string $groupName): StringList
    {
        return StringList::mapFrom(explode('And', $groupName), self::prettify(...));
    }

    private static function prettify(string $input): string
    {
        return self::TRANSLATIONS[$input] ?? $input;
    }
}
