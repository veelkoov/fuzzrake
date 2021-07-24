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
        self::KEY_CLOSED => 'closed?',
    ];

    public const OFFER_REGEXES = [
        'COMMISSIONS&QUOTES' => 'commissions? &amp; quotes?',
        'PARTS'              => 'parts? commissions?',
        'COMMISSIONS'        => 'comm?iss?ions?|custom slots?',
        'TRADES'             => 'trades?',
        'PRE-MADES'          => 'pre-made designs?',
        'ARTISTIC LIBERTY'   => 'artistic liberty',
        'QUOTES'             => 'quotes?',
        'CUSTOM ORDERS'      => 'custom orders?',
    ];

    public const COMMON_REGEXES = [
        'STATUS' => '(?<'.self::GRP_STATUS.'>(?:OPEN)|(?:CLOSED))',
        'OFFER'  => '(?<'.self::GRP_OFFER.'>(?:COMMISSIONS&QUOTES)|(?:PARTS)|(?:COMMISSIONS)|(?:TRADES)|(?:PRE-MADES)|(?:ARTISTIC LIBERTY)|(?:QUOTES)|(?:CUSTOM ORDERS))',
    ];

    public const FALSE_POSITIVES_REGEXES = [
        'next commissions? opening estimated',
        '(?:if|when|while) commissions are STATUS',
    ];

    public const OFFER_STATUS_REGEXES = [
        'OFFER(?: status)? ?:? STATUS',
        'OFFER are (?:(?:currently|now) )?STATUS',
        'STATUS for OFFER',
    ];
}
