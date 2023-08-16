<?php

declare(strict_types=1);

namespace App\Tests\ByCodeAnalysis;

use App\Data\Definitions\Fields\Fields;
use App\Tests\TestUtils\Paths;
use PHPUnit\Framework\TestCase;
use TRegx\CleanRegex\Exception\PatternException;
use TRegx\CleanRegex\Match\Detail;
use TRegx\CleanRegex\Pattern;

use function Psl\File\read;

/**
 * Don't judge, I'm having a lot of fun here!
 *
 * @small
 */
class ArtisanFieldsTest extends TestCase
{
    private Pattern $constructor;
    private Pattern $constructorParameter;

    public function setUp(): void
    {
        $this->constructor = Pattern::of('constructor\((?<parameters>(?:(?:readonly )?[a-z]+: [a-z]+(?:\[\])?,?\s*)+)\)', 'si');
        $this->constructorParameter = Pattern::of('(?:readonly )?(?<name>[a-z]+): [a-z]+(?<is_list>\[\])?(?:,|$)', 'i');
    }

    /**
     * @throws PatternException
     */
    public function testArtisanTsModel(): void
    {
        $modelSource = read(Paths::getArtisanTypeScriptClassPath());

        $parameters = $this->constructor
            ->match($modelSource)
            ->first()
            ->group('parameters')
            ->text();

        $matches = $this->constructorParameter->match($parameters);

        static::assertGreaterThan(0, $matches->count());

        $fieldsInJson = Fields::public()->asArray();

        foreach ($matches as $detail) {
            static::assertInstanceOf(Detail::class, $detail); // Silence PHPStan

            $field = array_shift($fieldsInJson);

            static::assertNotNull($field);
            static::assertEquals($field->modelName(), $detail->get('name'));
            static::assertEquals($field->isList(), $detail->matched('is_list'), "{$field->modelName()} should be a list");
        }

        static::assertEmpty($fieldsInJson);
    }
}
