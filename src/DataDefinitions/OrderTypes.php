<?php

declare(strict_types=1);

namespace App\DataDefinitions;

class OrderTypes extends Dictionary
{
    final public const HEAD = 'Head (as parts/separate)';
    final public const MINI_PARTIAL = 'Mini partial (head + handpaws + tail)';
    final public const PARTIAL = 'Partial (head + handpaws + tail + feetpaws)';
    final public const FULL_PLANTIGRADE = 'Full plantigrade';
    final public const FULL_DIGITIGRADE = 'Full digitigrade';
    final public const TAILS = 'Tails (as parts/separate)';
    final public const HANDPAWS = 'Handpaws (as parts/separate)';
    final public const FEETPAWS = 'Feetpaws (as parts/separate)';
    final public const THREE_FOURTH = 'Three-fourth (head + handpaws + tail + legs/pants + feetpaws)';
    final public const BODYSUITS = 'Bodysuits (as parts/separate)';

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
