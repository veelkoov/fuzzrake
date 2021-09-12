<?php

declare(strict_types=1);

namespace App\DataDefinitions;

class OrderTypes extends Dictionary
{
    public const HEAD = 'Head (as parts/separate)';
    public const MINI_PARTIAL = 'Mini partial (head + handpaws + tail)';
    public const PARTIAL = 'Partial (head + handpaws + tail + feetpaws)';
    public const FULL_PLANTIGRADE = 'Full plantigrade';
    public const FULL_DIGITIGRADE = 'Full digitigrade';
    public const TAILS = 'Tails (as parts/separate)';
    public const HANDPAWS = 'Handpaws (as parts/separate)';
    public const FEETPAWS = 'Feetpaws (as parts/separate)';
    public const THREE_FOURTH = 'Three-fourth (head + handpaws + tail + legs/pants + feetpaws)';
    public const BODYSUITS = 'Bodysuits (as parts/separate)';

    public static function getValues(): array
    {
        return [
            self::HEAD             => self::HEAD,
            self::MINI_PARTIAL     => self::MINI_PARTIAL,
            self::PARTIAL          => self::PARTIAL,
            self::FULL_PLANTIGRADE => self::FULL_PLANTIGRADE,
            self::FULL_DIGITIGRADE => self::FULL_DIGITIGRADE,
            self::TAILS            => self::TAILS,
            self::HANDPAWS         => self::HANDPAWS,
            self::FEETPAWS         => self::FEETPAWS,
            self::THREE_FOURTH     => self::THREE_FOURTH,
            self::BODYSUITS        => self::BODYSUITS,
        ];
    }
}
