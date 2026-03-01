<?php

declare(strict_types=1);

namespace App\Data\Definitions;

use Override;

final class OrderTypes extends Dictionary
{
    public const string HEAD = 'Head (as parts/separate)';
    public const string MINI_PARTIAL = 'Mini partial (head + handpaws + tail)';
    public const string PARTIAL = 'Partial (head + handpaws + tail + feetpaws)';
    public const string FULL_PLANTIGRADE = 'Full plantigrade';
    public const string FULL_DIGITIGRADE = 'Full digitigrade';
    public const string TAILS = 'Tails (as parts/separate)';
    public const string HANDPAWS = 'Handpaws (as parts/separate)';
    public const string FEETPAWS = 'Feetpaws (as parts/separate)';
    public const string THREE_FOURTH = 'Three-fourth (head + handpaws + tail + legs/pants + feetpaws)';
    public const string BODYSUITS = 'Bodysuits (as parts/separate)';

    #[Override]
    public static function getValues(): array
    {
        return [
            self::HEAD,
            self::MINI_PARTIAL,
            self::PARTIAL,
            self::FULL_PLANTIGRADE,
            self::FULL_DIGITIGRADE,
            self::TAILS,
            self::HANDPAWS,
            self::FEETPAWS,
            self::THREE_FOURTH,
            self::BODYSUITS,
        ];
    }
}
