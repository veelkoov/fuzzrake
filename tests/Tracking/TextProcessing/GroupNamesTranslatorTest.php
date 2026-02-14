<?php

declare(strict_types=1);

namespace App\Tests\Tracking\TextProcessing;

use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Tracking\TextProcessing\GroupNamesTranslator;
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
