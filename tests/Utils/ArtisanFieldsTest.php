<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Artisan\Fields;
use App\Utils\Regexp\Regexp;
use PHPUnit\Framework\TestCase;

/**
 * Don't judge, I'm having a lot of fun here!
 */
class ArtisanFieldsTest extends TestCase
{
    private const REGEXP_CONSTRUCTOR = '#constructor\((?<parameters>(?:(?:readonly )?[a-z]+: [a-z]+(?:\[\])?,?\s*)+)\)#si';
    private const REGEXP_CONSTRUCTOR_PARAMETER = '#(?:readonly )?(?<name>[a-z]+): [a-z]+(?<is_list>\[\])?(?:,|$)#i';

    public function testArtisanTsModel(): void
    {
        $modelSource = file_get_contents(__DIR__.'/../../assets/scripts/class/Artisan.ts');

        static::assertTrue(Regexp::match(self::REGEXP_CONSTRUCTOR, $modelSource, $constructorMatch));

        static::assertGreaterThan(0, Regexp::matchAll(self::REGEXP_CONSTRUCTOR_PARAMETER, $constructorMatch['parameters'], $parMatches));

        $fieldsInJson = Fields::inJson();

        foreach ($parMatches[0] as $idx => $_) {
            $field = array_shift($fieldsInJson);

            static::assertNotNull($field);
            static::assertEquals($field->modelName(), $parMatches['name'][$idx]);
            static::assertEquals($field->isList(), !empty($parMatches['is_list'][$idx]), "{$field->modelName()} should be a list");
        }

        static::assertEmpty($fieldsInJson);
    }
}
