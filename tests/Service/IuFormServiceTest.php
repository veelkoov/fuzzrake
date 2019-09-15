<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Utils\Artisan\Fields;
use App\Utils\Regexp\Utils;
use PHPUnit\Framework\TestCase;

/**
 * Don't judge, I'm having a lot of fun here!
 */
class IuFormServiceTest extends TestCase
{
    private const REGEXP_DATA_ITEM_PUSH = '#\s\d+ +=> (?:\$this->transform[a-z]+\()?\$artisan->get(?<name>[a-z]+)\(\)\)?,#i';

    public function testServiceCodeNaively(): void // TODO: Transform into proper test
    {
        $checkedSource = file_get_contents(__DIR__.'/../../src/Service/IuFormService.php');

        $this->assertGreaterThan(0, Utils::matchAll(self::REGEXP_DATA_ITEM_PUSH, $checkedSource, $matches));

        $fieldsInForm = Fields::exportedToIuForm();
        unset($fieldsInForm[Fields::VALIDATION_CHECKBOX]);

        foreach ($matches['name'] as $modelName) {
            $field = Fields::getByModelName(lcfirst($modelName));
            $name = $field->is(Fields::CONTACT_INFO_OBFUSCATED) ? Fields::CONTACT_INPUT_VIRTUAL : $field->name();

            $this->assertArrayHasKey($name, $fieldsInForm);

            unset($fieldsInForm[$name]);
        }

        $this->assertEmpty($fieldsInForm, 'Fields left to be matched: '.join(', ', $fieldsInForm));
    }
}
