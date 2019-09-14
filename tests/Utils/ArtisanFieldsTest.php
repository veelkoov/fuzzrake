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
    const REGEXP_CONSTRUCTOR = '#constructor\((?<parameters>(?:readonly [a-z]+: [a-z]+(?:\[\])?,?\s*)+)\)#si';
    const REGEXP_CONSTRUCTOR_PARAMETER = '#readonly (?<name>[a-z]+): [a-z]+(?<is_list>\[\])?(?:,|$)#i';
    const REGEXP_DATA_ITEM_PUSH = '#\s\d+: (?:this\.transform[a-z]+\()?artisan\.(?<name>[a-z]+)\)?,#i';

    public function testArtisanTsModel(): void
    {
        $modelSource = file_get_contents(__DIR__.'/../../assets/js/main/Artisan.ts');

        $this->assertTrue(Utils::match(self::REGEXP_CONSTRUCTOR, $modelSource, $constructorMatch));

        $this->assertGreaterThan(0, Utils::matchAll(self::REGEXP_CONSTRUCTOR_PARAMETER, $constructorMatch['parameters'], $parMatches));

        $fieldsInJson = Fields::inJson();

        foreach ($parMatches[0] as $idx => $_) {
            $field = array_shift($fieldsInJson);

            $this->assertNotNull($field);
            $this->assertEquals($field->modelName(), $parMatches['name'][$idx]);
            $this->assertEquals($field->isList(), !empty($parMatches['is_list'][$idx]));
        }

        $this->assertEmpty($fieldsInJson);
    }

    public function testGoogleFormsHelper(): void
    {
        $modelSource = file_get_contents(__DIR__.'/../../assets/js/main/GoogleFormsHelper.ts');

        $this->assertGreaterThan(0, Utils::matchAll(self::REGEXP_DATA_ITEM_PUSH, $modelSource, $matches));

        $fieldsInForm = Fields::exportedToIuForm();
        unset($fieldsInForm[Fields::VALIDATION_CHECKBOX]);

        foreach ($matches['name'] as $modelName) {
            $field = Fields::getByModelName($modelName);
            $name = $field->is(Fields::CONTACT_INFO_OBFUSCATED) ? Fields::CONTACT_INPUT_VIRTUAL : $field->name();

            $this->assertArrayHasKey($name, $fieldsInForm);

            unset($fieldsInForm[$name]);
        }

        $this->assertEmpty($fieldsInForm, 'Fields left to be matched: '.join($fieldsInForm));
    }
}
