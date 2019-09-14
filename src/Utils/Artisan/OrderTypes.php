<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

class OrderTypes
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
}
