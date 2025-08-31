<?php

declare(strict_types=1);

namespace App\Tests\Filtering\RequestsHandling;

use App\Filtering\RequestsHandling\Choices;
use App\Filtering\RequestsHandling\FiltersValidChoicesFilter;
use App\Service\DataService;
use App\Species\SpeciesService;
use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use Exception;
use PHPUnit\Framework\Attributes\Small;
use Veelkoov\Debris\Sets\StringSet;

#[Small]
class FiltersValidChoicesFilterTest extends FuzzrakeTestCase
{
    /**
     * @throws Exception
     */
    public function testGetOnlyAllowed(): void
    {
        $dataServiceMock = $this->createMock(DataService::class);
        $dataServiceMock->method('getCountries')->willReturn(StringSet::of('FI'));
        $dataServiceMock->method('getStates')->willReturn(StringSet::of('Liquid'));
        $dataServiceMock->method('getOpenFor')->willReturn(StringSet::of('Pancakes', 'Waffles'));
        $dataServiceMock->method('getLanguages')->willReturn(StringSet::of('Czech', 'Finnish'));
        $speciesServiceMock = $this->createMock(SpeciesService::class);
        $speciesServiceMock->method('getValidNames')->willReturn(StringSet::of('Birds'));

        $subject = new FiltersValidChoicesFilter($dataServiceMock, $speciesServiceMock);

        $choices = new Choices(
            '',
            '',
            StringSet::of('FI', '?', 'UK', '*'),
            StringSet::of('Liquid', '?', 'Solid', '*'),
            StringSet::of('Finnish', 'Czech', '?', 'English', '*'),
            StringSet::of('Toony', '?', '*', 'Yellow', '!'),
            StringSet::of('LED eyes', '?', '*', 'Oven', '!'),
            StringSet::of('Full plantigrade', '?', '*', 'Pancakes', '!'),
            StringSet::of('Standard commissions', '?', 'Waffles', '*'),
            StringSet::of('Pancakes', '!', '-', 'Kettles', '*'),
            StringSet::of('Birds', '?', 'Furniture', '*'),
            StringSet::of('None', 'Not supported', 'Supported', '?', '*', 'Waffles', ''),
            false, false, false, false, 1);

        $result = $subject->getOnlyValidChoices($choices);

        self::assertSameItems(['FI', '?'], $result->countries);
        self::assertSameItems(['Liquid', '?'], $result->states);
        self::assertSameItems(['Finnish', 'Czech', '?'], $result->languages);
        self::assertSameItems(['Toony', '?', '*'], $result->styles);
        self::assertSameItems(['LED eyes', '?', '*'], $result->features);
        self::assertSameItems(['Full plantigrade', '?', '*'], $result->orderTypes);
        self::assertSameItems(['Standard commissions', '?'], $result->productionModels);
        self::assertSameItems(['Pancakes', '!', '-'], $result->openFor);
        self::assertSameItems(['Birds', '?'], $result->species);
        self::assertSameItems(['Not supported', 'Supported', '?'], $result->paymentPlans);
    }
}
