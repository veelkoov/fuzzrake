<?php

declare(strict_types=1);

namespace App\Tests\DataDefinitions;

use App\DataDefinitions\Fields;
use PHPUnit\Framework\TestCase;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;

class FieldsTest extends TestCase
{
    private const FIELD_NAME_REGEXP = 'const +(?<name>[A-Z_0-9]+) +=';

    /**
     * Purpose of this test is to make sure no field definition is accidentally left out.
     *
     * @throws NonexistentGroupException
     */
    public function testIfTheDefinitionsAreComplete(): void
    {
        $classPath = str_replace(['App\\', '\\'], '/', Fields::class).'.php';
        $pathToRoot = str_repeat('../', substr_count(self::class, '\\') - 1);
        $modelSource = file_get_contents(__DIR__."/$pathToRoot/src/$classPath");

        $fields = Fields::getAll();

        pattern(self::FIELD_NAME_REGEXP, 'i')->match($modelSource)->forEach(function (Detail $match) use ($fields): void {
            $name = $match->get('name');

            self::assertArrayHasKey($name, $fields->asArray());
            self::assertEquals($name, Fields::get($name)->name());
        });
    }
}
