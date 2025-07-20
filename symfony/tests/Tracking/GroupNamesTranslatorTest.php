<?php

declare(strict_types=1);

namespace App\Tests\Tracking;

use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Tracking\GroupNamesTranslator;
use PHPUnit\Framework\Attributes\Small;

#[Small]
class GroupNamesTranslatorTest extends FuzzrakeTestCase
{
    public function testToOffers(): void
    {
        self::assertSameItems(
            ['Commissions'],
            GroupNamesTranslator::toOffers('Commissions'),
        );

        self::assertSameItems(
            ['Handpaws commissions', 'Sockpaws commissions'],
            GroupNamesTranslator::toOffers('HandpawsCmsAndSockpawsCms'),
        );

        self::assertSameItems(
            ['Commissions', 'Quotes'],
            GroupNamesTranslator::toOffers('CommissionsAndQuotes'),
        );
    }
}
