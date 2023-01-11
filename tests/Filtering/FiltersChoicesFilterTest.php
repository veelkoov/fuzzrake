<?php

declare(strict_types=1);

namespace App\Tests\Filtering;

use App\Filtering\Choices;
use App\Filtering\FiltersChoicesFilter;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Exception;

class FiltersChoicesFilterTest extends KernelTestCaseWithEM
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

        $subject = self::getContainer()->get(FiltersChoicesFilter::class);
        self::assertInstanceOf(FiltersChoicesFilter::class, $subject);

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
            ['Birds', '?', 'Furniture', '*'], // TODO: Other/unknown is not implemented
            false, false, false, false, false);

        $result = $subject->getOnlyAllowed($choices);

        self::assertEquals(['FI', '?'], $result->countries);
        self::assertEquals(['Liquid', '?'], $result->states);
        self::assertEquals(['Finnish', 'Czech', '?'], $result->languages);
        self::assertEquals(['Toony', '?', '*'], $result->styles);
        self::assertEquals(['LED eyes', '?', '*'], $result->features);
        self::assertEquals(['Full plantigrade', '?', '*'], $result->orderTypes);
        self::assertEquals(['Standard commissions', '?'], $result->productionModels);
        self::assertEquals(['Pancakes', '!', '-'], $result->commissionStatuses);
        self::assertEquals(['Birds', '?'], $result->species);
    }
}
