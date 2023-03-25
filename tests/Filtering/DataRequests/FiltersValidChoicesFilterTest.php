<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests;

use App\Filtering\DataRequests\Choices;
use App\Filtering\DataRequests\FiltersValidChoicesFilter;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Exception;

class FiltersValidChoicesFilterTest extends KernelTestCaseWithEM
{
    /**
     * @throws Exception
     */
    public function testGetOnlyAllowed(): void
    {
        self::bootKernel();

        $artisan = Artisan::new()
            ->setLanguages("Czech\nFinnish")
            ->setOpenFor("Pancakes\nWaffles")
            ->setCountry('FI')
            ->setState('Liquid');

        self::persistAndFlush($artisan);

        $subject = self::getContainer()->get(FiltersValidChoicesFilter::class);
        self::assertInstanceOf(FiltersValidChoicesFilter::class, $subject);

        $choices = new Choices(
            '',
            ['FI', '?', 'UK', '*'],
            ['Liquid', '?', 'Solid', '*'],
            ['Finnish', 'Czech', '?', 'English', '*'],
            ['Toony', '?', '*', 'Yellow', '!'],
            ['LED eyes', '?', '*', 'Oven', '!'],
            ['Full plantigrade', '?', '*', 'Pancakes', '!'],
            ['Standard commissions', '?', 'Waffles', '*'],
            ['Pancakes', '!', '-', 'Kettles', '*'],
            ['Birds', '?', 'Furniture', '*'],
            false, false, false, false, false, false);

        $result = $subject->getOnlyValidChoices($choices);

        self::assertEquals(['FI', '?'], $result->countries);
        self::assertEquals(['Liquid', '?'], $result->states);
        self::assertEquals(['Finnish', 'Czech', '?'], $result->languages);
        self::assertEquals(['Toony', '?', '*'], $result->styles);
        self::assertEquals(['LED eyes', '?', '*'], $result->features);
        self::assertEquals(['Full plantigrade', '?', '*'], $result->orderTypes);
        self::assertEquals(['Standard commissions', '?'], $result->productionModels);
        self::assertEquals(['Pancakes', '!', '-'], $result->openFor);
        self::assertEquals(['Birds', '?'], $result->species);
    }
}
