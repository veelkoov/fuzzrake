<?php

declare(strict_types=1);

namespace App\Tests\Utils\Artisan;

use App\DataDefinitions\Fields;
use PHPUnit\Framework\TestCase;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;

class FieldsTest extends TestCase
{
    private const FIELD_NAME_REGEXP = 'const +(?<name>[A-Z_0-9]+) +=';
    public const SRC_DIR_PATH = __DIR__.'/../../../src';

    /**
     * Purpose of this test is to make sure no field definition is accidentally left out.
     *
     * @throws NonexistentGroupException
     */
    public function testIfTheDefinitionsAreComplete(): void
    {
        $classPath = str_replace(['App\\', '\\'], '/', Fields::class).'.php';
        $modelSource = file_get_contents(self::SRC_DIR_PATH.'/'.$classPath);

        $fields = Fields::getAll();

        pattern(self::FIELD_NAME_REGEXP, 'i')->match($modelSource)->forEach(function (Detail $match) use ($fields): void {
            $name = $match->get('name');

            self::assertArrayHasKey($name, $fields->asArray());
            self::assertEquals($name, Fields::get($name)->name());
        });
    }
}
