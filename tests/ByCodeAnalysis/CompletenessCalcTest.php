<?php

declare(strict_types=1);

namespace App\Tests\ByCodeAnalysis;

use App\DataDefinitions\Fields\Fields;
use App\Tests\TestUtils\Paths;
use PHPUnit\Framework\TestCase;

class CompletenessCalcTest extends TestCase
{
    public function testAllFieldsCovered(): void
    {
        $contents = file_get_contents(Paths::getCompletenessCalcClassPath());
        $wrongCount = [];

        foreach (Fields::all() as $field) {
            if (1 !== pattern('[ :]'.$field->value.'[,;).]')->count($contents)) {
                $wrongCount[] = $field->value;
            }
        }

        self::assertEmpty($wrongCount, 'Wrong number of appearances: '.implode(', ', $wrongCount));
    }
}
