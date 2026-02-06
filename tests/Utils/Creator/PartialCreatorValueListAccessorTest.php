<?php

declare(strict_types=1);

namespace App\Tests\Utils\Creator;

use App\Data\Definitions\Fields\Field;
use App\Entity\Creator as CreatorE;
use App\Entity\CreatorValue;
use App\Utils\Creator\PartialCreatorValueListAccessor;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class PartialCreatorValueListAccessorTest extends TestCase
{
    public function testGetAndSet(): void
    {
        $creator = new Creator();
        $creatorE = $creator->entity;

        $creatorE->addValue(new CreatorValue()->setFieldName('OtherField')->setValue('OtherField value 1'));
        $creatorE->addValue(new CreatorValue()->setFieldName('OtherField')->setValue('OtherField value 2'));

        self::assertUnorderedValues($creatorE, [
            'OtherField: OtherField value 1',
            'OtherField: OtherField value 2',
        ]);

        // Test adding to empty

        PartialCreatorValueListAccessor::set($creator, Field::FEATURES, [
            'Feature 1',
            'Feature 2',
            'Feature 3',
        ]);

        self::assertUnorderedValues($creatorE, [
            'OtherField: OtherField value 1',
            'OtherField: OtherField value 2',
            'FEATURES: Feature 1',
            'FEATURES: Feature 2',
            'FEATURES: Feature 3',
        ]);

        self::assertEquals([
            'Feature 1',
            'Feature 2',
            'Feature 3',
        ], PartialCreatorValueListAccessor::get($creator, Field::FEATURES));

        // Test adding new to non-empty

        PartialCreatorValueListAccessor::set($creator, Field::FEATURES, [
            'Feature 1',
            'Feature 1.5',
            'Feature 2',
            'Feature 3',
            'Feature 4',
        ]);

        self::assertUnorderedValues($creatorE, [
            'OtherField: OtherField value 1',
            'OtherField: OtherField value 2',
            'FEATURES: Feature 1',
            'FEATURES: Feature 1.5',
            'FEATURES: Feature 2',
            'FEATURES: Feature 3',
            'FEATURES: Feature 4',
        ]);

        self::assertEquals([
            'Feature 1',
            'Feature 1.5',
            'Feature 2',
            'Feature 3',
            'Feature 4',
        ], PartialCreatorValueListAccessor::get($creator, Field::FEATURES));

        // Test removing from non-empty

        PartialCreatorValueListAccessor::set($creator, Field::FEATURES, [
            'Feature 1.5',
            'Feature 2',
        ]);

        self::assertUnorderedValues($creatorE, [
            'OtherField: OtherField value 1',
            'OtherField: OtherField value 2',
            'FEATURES: Feature 1.5',
            'FEATURES: Feature 2',
        ]);

        self::assertEquals([
            'Feature 1.5',
            'Feature 2',
        ], PartialCreatorValueListAccessor::get($creator, Field::FEATURES));

        // Test adding and removing from non-empty

        PartialCreatorValueListAccessor::set($creator, Field::FEATURES, [
            'Feature 1',
            'Feature 2',
            'Feature 3',
        ]);

        self::assertUnorderedValues($creatorE, [
            'OtherField: OtherField value 1',
            'OtherField: OtherField value 2',
            'FEATURES: Feature 1',
            'FEATURES: Feature 2',
            'FEATURES: Feature 3',
        ]);

        self::assertEquals([
            'Feature 1',
            'Feature 2',
            'Feature 3',
        ], PartialCreatorValueListAccessor::get($creator, Field::FEATURES));

        // Test emptying non-empty

        PartialCreatorValueListAccessor::set($creator, Field::FEATURES, [
        ]);

        self::assertUnorderedValues($creatorE, [
            'OtherField: OtherField value 1',
            'OtherField: OtherField value 2',
        ]);

        self::assertEquals([
        ], PartialCreatorValueListAccessor::get($creator, Field::FEATURES));

        // Test emptying empty

        PartialCreatorValueListAccessor::set($creator, Field::FEATURES, [
        ]);

        self::assertUnorderedValues($creatorE, [
            'OtherField: OtherField value 1',
            'OtherField: OtherField value 2',
        ]);

        self::assertEquals([
        ], PartialCreatorValueListAccessor::get($creator, Field::FEATURES));
    }

    /**
     * @param list<string> $expected
     */
    private static function assertUnorderedValues(CreatorE $creator, array $expected): void
    {
        $actual = $creator->getValues()
            ->map(fn (CreatorValue $value): string => "{$value->getFieldName()}: {$value->getValue()}")
            ->toArray();
        sort($actual);

        sort($expected);

        self::assertEquals($expected, $actual);
    }
}
