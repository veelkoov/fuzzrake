<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests;

use App\Filtering\DataRequests\Filters\SpecialItemsExtractor;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Veelkoov\Debris\StringList;

/**
 * @small
 */
class SpecialItemsExtractorTest extends TestCase
{
    public function testExtracting(): void
    {
        $subject = new SpecialItemsExtractor(new StringList('aaa', 'bbb', '111'), '111', '222');

        self::assertEquals(['aaa', 'bbb'], $subject->common->toArray());
        self::assertTrue($subject->hasSpecial('111'));
        self::assertFalse($subject->hasSpecial('222'));

        try {
            $subject->hasSpecial('333');
        } catch (InvalidArgumentException) {
            // Expected
        }
    }
}
