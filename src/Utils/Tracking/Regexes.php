<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\Traits\UtilityClass;

final class Regexes
{
    use UtilityClass;

    public const GRP_STATUS = 'status';
    public const GRP_OFFER = 'offer';

    public const KEY_OPEN = 'OPEN';
    public const KEY_CLOSED = 'CLOSED';

    public const STATUS_REGEXES = [
        self::KEY_OPEN   => 'open',
        self::KEY_CLOSED => 'closed',
    ];

    public const OFFER_REGEXES = [
        'COMMISSIONS' => 'commissions?',
        'TRADES'      => 'trades?',
    ];

    public const COMMON_REGEXES = [
        'STATUS' => '(?<'.self::GRP_STATUS.'>(?:OPEN)|(?:CLOSED))',
        'OFFER'  => '(?<'.self::GRP_OFFER.'>(?:COMMISSIONS)|(?:TRADES))',
    ];

    public const FALSE_POSITIVES_REGEXES = [
        // TODO
    ];

    public const OFFER_STATUS_REGEXES = [
        'OFFER status: STATUS',
    ];
}
