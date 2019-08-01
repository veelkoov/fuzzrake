<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\ArtisanFields;
use App\Utils\Regexp\Utils;
use PHPUnit\Framework\TestCase;

/**
 * Don't judge, I'm having a lot of fun here!
 */
class ArtisanFieldsTest extends TestCase
{
    public function testArtisanTsModel(): void
    {
        $modelSource = file_get_contents(__DIR__.'/../../assets/js/main/Artisan.ts');

        $this->assertTrue(Utils::match('#constructor\((?<parameters>(?:readonly [a-z]+: [a-z]+(?:\[\])?,?\s*)+)\)#si', $modelSource, $constructorMatch));

        Utils::matchAll('#readonly (?<name>[a-z]+): [a-z]+(?<is_list>\[\])?(?:,|$)#si', $constructorMatch['parameters'], $parMatches);

        $fieldsInJson = ArtisanFields::inJson();

        foreach ($parMatches[0] as $idx => $_) {
            $field = array_shift($fieldsInJson);

            $this->assertEquals($field->modelName(), $parMatches['name'][$idx]);
            $this->assertEquals($field->isList(), !empty($parMatches['is_list'][$idx]));
        }

        $this->assertEmpty($fieldsInJson);
    }
}
