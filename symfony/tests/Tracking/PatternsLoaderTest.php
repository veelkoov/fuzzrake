<?php

declare(strict_types=1);

namespace App\Tests\Tracking;

use App\Tests\TestUtils\DataDefinitions;
use App\Tracking\PatternsLoader;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class PatternsLoaderTest extends TestCase
{
    public function testBuiltInConfigurationLoads(): void
    {
        $this->expectNotToPerformAssertions();

        $trackingDefinitions = DataDefinitions::get('tracking.yaml', 'tracking');
        new PatternsLoader($trackingDefinitions); // @phpstan-ignore argument.type
    }

    public function testPlaceholders(): void
    {
        $input = [
            'placeholders' => [
                'OPEN_TAG' => ['<tag>'],
                'END_TAG' => ['</tag>'],
                'END_OPEN_TAGS' => ['END_TAG\s*OPEN_TAG'],
                'STATUS=Status' => ['open', 'closed'],
                'OFFER' => [
                    'COMMISSIONS=Commissions' => ['commissions'],
                    'QUOTES=Quotes' => ['quotes'],
                ],
            ],
            'cleaners' => [],
            'false_positives' => [],
            'offers_statuses' => ['END_OPEN_TAGS OFFER: STATUS END_TAG'],
        ];

        $expectedOffersStatuses = [
            '((</tag>)\s*(<tag>)) ((?P<Commissions>commissions)|(?P<Quotes>quotes)): (?P<Status>open|closed) (</tag>)',
        ];

        $subject = new PatternsLoader($input);
        $result = $subject->offersStatuses->getValuesArray();

        self::assertEqualsCanonicalizing($expectedOffersStatuses, $result);
    }
}
