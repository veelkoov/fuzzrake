<?php

declare(strict_types=1);

namespace App\Data\Definitions;

use Override;

class ProductionModels extends Dictionary
{
    final public const string STANDARD_COMMISSIONS = 'Standard commissions';
    final public const string ARTISTIC_LIBERTY_COMMISSIONS = 'Artistic liberty commissions';
    final public const string PREMADES = 'Premades';

    #[Override]
    public static function getValues(): array
    {
        return [
            self::STANDARD_COMMISSIONS,
            self::ARTISTIC_LIBERTY_COMMISSIONS,
            self::PREMADES,
        ];
    }
}
