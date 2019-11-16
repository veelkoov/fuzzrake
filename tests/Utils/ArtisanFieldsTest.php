<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Artisan\Fields;
use App\Utils\Regexp\Utils;
use PHPUnit\Framework\TestCase;

/**
 * Don't judge, I'm having a lot of fun here!
 */
class ArtisanFieldsTest extends TestCase
{
    private const REGEXP_CONSTRUCTOR = '#constructor\((?<parameters>(?:readonly [a-z]+: [a-z]+(?:\[\])?,?\s*)+)\)#si';
    private const REGEXP_CONSTRUCTOR_PARAMETER = '#readonly (?<name>[a-z]+): [a-z]+(?<is_list>\[\])?(?:,|$)#i';

    public function testArtisanTsModel(): void
    {
        $modelSource = file_get_contents(__DIR__.'/../../assets/js/class/Artisan.ts');

        $this->assertTrue(Utils::match(self::REGEXP_CONSTRUCTOR, $modelSource, $constructorMatch));

        $this->assertGreaterThan(0, Utils::matchAll(self::REGEXP_CONSTRUCTOR_PARAMETER, $constructorMatch['parameters'], $parMatches));

        $fieldsInJson = Fields::inJson();

        foreach ($parMatches[0] as $idx => $_) {
            $field = array_shift($fieldsInJson);

            $this->assertNotNull($field);
            static::assertEquals($field->modelName(), $parMatches['name'][$idx]);
            static::assertEquals($field->isList(), !empty($parMatches['is_list'][$idx]));
        }

        $this->assertEmpty($fieldsInJson);
    }
}
