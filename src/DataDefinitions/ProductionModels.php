<?php

declare(strict_types=1);

namespace App\DataDefinitions;

class ProductionModels extends Dictionary
{
    public const STANDARD_COMMISSIONS = 'Standard commissions';
    public const ARTISTIC_LIBERTY_COMMISSIONS = 'Artistic liberty commissions';
    public const PREMADES = 'Premades';

    public static function getValues(): array
    {
        return [
            self::STANDARD_COMMISSIONS         => self::STANDARD_COMMISSIONS,
            self::ARTISTIC_LIBERTY_COMMISSIONS => self::ARTISTIC_LIBERTY_COMMISSIONS,
            self::PREMADES                     => self::PREMADES,
        ];
    }
}
