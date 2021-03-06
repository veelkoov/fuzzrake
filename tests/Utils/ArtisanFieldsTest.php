<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Artisan\Fields;
use PHPUnit\Framework\TestCase;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;

/**
 * Don't judge, I'm having a lot of fun here!
 */
class ArtisanFieldsTest extends TestCase
{
    private const REGEXP_CONSTRUCTOR = 'constructor\((?<parameters>(?:(?:readonly )?[a-z]+: [a-z]+(?:\[\])?,?\s*)+)\)';
    private const REGEXP_CONSTRUCTOR_PARAMETER = '(?:readonly )?(?<name>[a-z]+): [a-z]+(?<is_list>\[\])?(?:,|$)';

    /**
     * @throws NonexistentGroupException
     */
    public function testArtisanTsModel(): void
    {
        $modelSource = file_get_contents(__DIR__.'/../../assets/scripts/class/Artisan.ts');

        $parameters = pattern(self::REGEXP_CONSTRUCTOR, 'si')
            ->match($modelSource)
            ->findFirst(fn (Detail $detail): string => $detail->get('parameters'))
            ->orThrow();

        $matches = pattern(self::REGEXP_CONSTRUCTOR_PARAMETER, 'i')->match($parameters);

        static::assertGreaterThan(0, $matches->count());

        $fieldsInJson = Fields::public();

        $matches->forEach(function (Detail $detail) use (&$fieldsInJson): void {
            $field = array_shift($fieldsInJson);

            static::assertNotNull($field);
            static::assertEquals($field->modelName(), $detail->get('name'));
            static::assertEquals($field->isList(), $detail->matched('is_list'), "{$field->modelName()} should be a list");
        });

        static::assertEmpty($fieldsInJson);
    }
}
