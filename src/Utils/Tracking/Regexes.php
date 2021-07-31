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
        self::KEY_OPEN   => '(?:open(?!ing)|only making)',
        self::KEY_CLOSED => '(?:closed?|not accepting)',
    ];

    public const OFFER_REGEXES = [
        'HANDPAWS COMMISSIONS&SOCKPAWS COMMISSIONS' => 'handpaws_AND_sockpaws C___S',

        'PARTS&REFURBISHMENTS' => 'small/single parts_AND_refurbishments C___S',
        'COMMISSIONS&QUOTES'   => '(?:C___S_AND_quotes?)|(?:quotes?_AND_C___S)',
        'FULLSUIT COMMISSIONS' => 'fullsuit C___S',
        'PARTIAL COMMISSIONS'  => 'partial C___S',
        'HEAD COMMISSIONS'     => 'head C___S',
        'PARTS'                => '(?:fursuit )?parts? C___S',
        'COMMISSIONS'          => '(?:fursuit )?C___S|(?:custom )?slots?|fursuits?(?: queue)?|comms|current mode', // "current mode" - test case 052
        'TRADES'               => 'trades?',
        'REFURBISHMENTS'       => 'refurbishments?',
        'PRE-MADES'            => 'pre-?mades?(?: designs?)?',
        'ARTISTIC LIBERTY'     => 'artistic liberty',
        'QUOTES'               => 'quotes?',
        'CUSTOM ORDERS'        => 'custom orders?',
    ];

    public const COMMON_REGEXES = [
        '_AND_'  => '(?: and | ?(?:&amp;|/) ?)',
        'NOW'    => '(?:currently|now|always)',
        'C___S'  => '(?:comm?iss?ions?)', // Not including "comms"
        'STATUS' => '(?<'.self::GRP_STATUS.'>(?:OPEN)|(?:CLOSED))',
        'OFFER'  => '(?<'.self::GRP_OFFER.'>(?:HANDPAWS COMMISSIONS&SOCKPAWS COMMISSIONS)|(?:PARTS&REFURBISHMENTS)|(?:COMMISSIONS&QUOTES)|(?:FULLSUIT COMMISSIONS)|(?:PARTIAL COMMISSIONS)|(?:HEAD COMMISSIONS)|(?:PARTS)|(?:COMMISSIONS)|(?:TRADES)|(?:PRE-MADES)|(?:ARTISTIC LIBERTY)|(?:QUOTES)|(?:CUSTOM ORDERS))',
    ];

    public const FALSE_POSITIVES_REGEXES = [
        'next C___S opening (?:estimated|will)',
        '(?:if|when|while) (?:C___S|quotes) are STATUS',
        'when (?:i\'m|i|we\'re|we) open for C___S',
        'C___S open in',
        'slots are open in',
        'as slots open',
        '(?:>| )art C___S(?: are:?| ?:) STATUS',
    ];

    public const OFFER_STATUS_REGEXES = [
        '(?:C___S\s*[:-]\s*)?STATUS for (?:new )?OFFER',

        'OFFER(?: status| NOW)?(?: | ?[:_-]\s*?)STATUS', // - and _ should work for attributes as well
        'OFFER\s+(?:are:?|basically)\s+(?:NOW:?\s+)?STATUS',

        'NOW (?:is|are|am) STATUS new OFFER',
        'NOW STATUS OFFER',

        '\[ OFFER[. ]+STATUS \]', // Test case 045

        '<h2[^>]*> ?OFFER \| STATUS ?</h2>',
        '<h2[^>]*> ?OFFER(?:(?: status:?| ?:)) ?</h2>\s*<h2[^>]*> ?STATUS', // No closing </h2> for any comments
        '<p[^>]*> ?OFFER(?:(?: status:?| ?:)) ?</p>\s*<p[^>]*> ?NOW STATUS', // No closing </p> for any comments
    ];
}
