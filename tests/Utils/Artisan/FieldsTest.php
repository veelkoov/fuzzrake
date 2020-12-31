<?php

declare(strict_types=1);

namespace App\Tests\Utils\Artisan;

use App\Utils\Artisan\Fields;
use PHPUnit\Framework\TestCase;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Match;

class FieldsTest extends TestCase
{
    private const FIELD_NAME_REGEXP = 'const +(?<name>[A-Z_0-9]+) +=';
    private const FIELDS_CLASS_FILEPATH = __DIR__.'/../../../src/Utils/Artisan/Fields.php';

    /**
     * Purpose of this test is to make sure no field definition is accidentally left out.
     *
     * @throws NonexistentGroupException
     */
    public function testIfTheDefinitionsAreComplete(): void
    {
        $modelSource = file_get_contents(self::FIELDS_CLASS_FILEPATH);
        $fields = Fields::getAll();

        pattern(self::FIELD_NAME_REGEXP, 'i')->match($modelSource)->forEach(function (Match $match) use ($fields): void {
            $name = $match->get('name');

            self::assertArrayHasKey($name, $fields);
            self::assertEquals($name, Fields::get($name)->name());
        });
    }
}
