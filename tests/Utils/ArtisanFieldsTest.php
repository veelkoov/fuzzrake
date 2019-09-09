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
    const REGEXP_CONSTRUCTOR = '#constructor\((?<parameters>(?:readonly [a-z]+: [a-z]+(?:\[\])?,?\s*)+)\)#si';
    const REGEXP_CONSTRUCTOR_PARAMETER = '#readonly (?<name>[a-z]+): [a-z]+(?<is_list>\[\])?(?:,|$)#i';
    const REGEXP_DATA_ITEM_PUSH = '#\s\d+: (?:this\.transform[a-z]+\()?artisan\.(?<name>[a-z]+)\)?,#i';

    public function testArtisanTsModel(): void
    {
        $modelSource = file_get_contents(__DIR__.'/../../assets/js/main/Artisan.ts');

        $this->assertTrue(Utils::match(self::REGEXP_CONSTRUCTOR, $modelSource, $constructorMatch));

        $this->assertGreaterThan(0, Utils::matchAll(self::REGEXP_CONSTRUCTOR_PARAMETER, $constructorMatch['parameters'], $parMatches));

        $fieldsInJson = ArtisanFields::inJson();

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

        $fieldsInForm = ArtisanFields::inIuForm();
        unset($fieldsInForm[ArtisanFields::IGNORED_IU_FORM_FIELD]);
        unset($fieldsInForm[ArtisanFields::PASSCODE]);
        unset($fieldsInForm[ArtisanFields::TIMESTAMP]);

        foreach ($matches['name'] as $modelName) {
            $field = ArtisanFields::getByModelName($modelName);

            $name = (ArtisanFields::CONTACT_ADDRESS_OBFUSCATED === $field->name()) // TODO: Find a better way
                ? ArtisanFields::ORIGINAL_CONTACT_INFO : $field->name();

            $this->assertArrayHasKey($name, $fieldsInForm);

            unset($fieldsInForm[$name]);
        }

        $this->assertEmpty($fieldsInForm);
    }
}
