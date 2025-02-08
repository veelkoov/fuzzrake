<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests;

use App\Filtering\DataRequests\Choices;
use App\Filtering\DataRequests\FiltersValidChoicesFilter;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Collections\StringList;
use Exception;

/**
 * @medium
 */
class FiltersValidChoicesFilterTest extends KernelTestCaseWithEM
{
    /**
     * @throws Exception
     */
    public function testGetOnlyAllowed(): void
    {
        self::bootKernel();

        $artisan = Artisan::new()
            ->setLanguages(['Czech', 'Finnish'])
            ->setOpenFor(['Pancakes', 'Waffles'])
            ->setCountry('FI')
            ->setState('Liquid');

        self::persistAndFlush($artisan);

        $subject = self::getContainer()->get(FiltersValidChoicesFilter::class);
        self::assertInstanceOf(FiltersValidChoicesFilter::class, $subject);

        $choices = new Choices(
            '',
            '',
            StringList::of('FI', '?', 'UK', '*'),
            StringList::of('Liquid', '?', 'Solid', '*'),
            StringList::of('Finnish', 'Czech', '?', 'English', '*'),
            StringList::of('Toony', '?', '*', 'Yellow', '!'),
            StringList::of('LED eyes', '?', '*', 'Oven', '!'),
            StringList::of('Full plantigrade', '?', '*', 'Pancakes', '!'),
            StringList::of('Standard commissions', '?', 'Waffles', '*'),
            StringList::of('Pancakes', '!', '-', 'Kettles', '*'),
            StringList::of('Birds', '?', 'Furniture', '*'),
            false, false, false, false, false, false, false, 1);

        $result = $subject->getOnlyValidChoices($choices);

        self::assertEquals(['FI', '?'], $result->countries->toArray());
        self::assertEquals(['Liquid', '?'], $result->states->toArray());
        self::assertEquals(['Finnish', 'Czech', '?'], $result->languages->toArray());
        self::assertEquals(['Toony', '?', '*'], $result->styles->toArray());
        self::assertEquals(['LED eyes', '?', '*'], $result->features->toArray());
        self::assertEquals(['Full plantigrade', '?', '*'], $result->orderTypes->toArray());
        self::assertEquals(['Standard commissions', '?'], $result->productionModels->toArray());
        self::assertEquals(['Pancakes', '!', '-'], $result->openFor->toArray());
        self::assertEquals(['Birds', '?'], $result->species->toArray());
    }
}
