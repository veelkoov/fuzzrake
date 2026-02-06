<?php

declare(strict_types=1);

namespace App\Data\Definitions;

use Override;

class OrderTypes extends Dictionary
{
    final public const string HEAD = 'Head (as parts/separate)';
    final public const string MINI_PARTIAL = 'Mini partial (head + handpaws + tail)';
    final public const string PARTIAL = 'Partial (head + handpaws + tail + feetpaws)';
    final public const string FULL_PLANTIGRADE = 'Full plantigrade';
    final public const string FULL_DIGITIGRADE = 'Full digitigrade';
    final public const string TAILS = 'Tails (as parts/separate)';
    final public const string HANDPAWS = 'Handpaws (as parts/separate)';
    final public const string FEETPAWS = 'Feetpaws (as parts/separate)';
    final public const string THREE_FOURTH = 'Three-fourth (head + handpaws + tail + legs/pants + feetpaws)';
    final public const string BODYSUITS = 'Bodysuits (as parts/separate)';

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
