<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests;

use App\Filtering\DataRequests\Choices;
use App\Filtering\DataRequests\FiltersValidChoicesFilter;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Exception;
use Veelkoov\Debris\StringList;

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
            StringList::from(['FI', '?', 'UK', '*']),
            StringList::from(['Liquid', '?', 'Solid', '*']),
            StringList::from(['Finnish', 'Czech', '?', 'English', '*']),
            StringList::from(['Toony', '?', '*', 'Yellow', '!']),
            StringList::from(['LED eyes', '?', '*', 'Oven', '!']),
            StringList::from(['Full plantigrade', '?', '*', 'Pancakes', '!']),
            StringList::from(['Standard commissions', '?', 'Waffles', '*']),
            StringList::from(['Pancakes', '!', '-', 'Kettles', '*']),
            StringList::from(['Birds', '?', 'Furniture', '*']),
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
