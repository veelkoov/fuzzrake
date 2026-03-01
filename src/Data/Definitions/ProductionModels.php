<?php

declare(strict_types=1);

namespace App\Data\Definitions;

use Override;

final class ProductionModels extends Dictionary
{
    public const string STANDARD_COMMISSIONS = 'Standard commissions';
    public const string ARTISTIC_LIBERTY_COMMISSIONS = 'Artistic liberty commissions';
    public const string PREMADES = 'Premades';

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
