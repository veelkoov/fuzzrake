<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests;

use App\Filtering\DataRequests\Choices;
use App\Filtering\DataRequests\FiltersValidChoicesFilter;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Exception;
use PHPUnit\Framework\Attributes\Medium;
use Veelkoov\Debris\StringSet;

#[Medium]
class FiltersValidChoicesFilterTest extends FuzzrakeKernelTestCase
{
    /**
     * @throws Exception
     */
    public function testGetOnlyAllowed(): void
    {
        $creator = new Creator()
            ->setLanguages(['Czech', 'Finnish'])
            ->setOpenFor(['Pancakes', 'Waffles'])
            ->setCountry('FI')
            ->setState('Liquid');

        self::persistAndFlush($creator);

        $subject = self::getContainerService(FiltersValidChoicesFilter::class);

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
            false, false, false, false, false, false, false, 1);

        $result = $subject->getOnlyValidChoices($choices);

        self::assertEquals(['FI', '?'], $result->countries->getValuesArray());
        self::assertEquals(['Liquid', '?'], $result->states->getValuesArray());
        self::assertEquals(['Finnish', 'Czech', '?'], $result->languages->getValuesArray());
        self::assertEquals(['Toony', '?', '*'], $result->styles->getValuesArray());
        self::assertEquals(['LED eyes', '?', '*'], $result->features->getValuesArray());
        self::assertEquals(['Full plantigrade', '?', '*'], $result->orderTypes->getValuesArray());
        self::assertEquals(['Standard commissions', '?'], $result->productionModels->getValuesArray());
        self::assertEquals(['Pancakes', '!', '-'], $result->openFor->getValuesArray());
        self::assertEquals(['Birds', '?'], $result->species->getValuesArray());
    }
}
