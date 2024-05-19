<?php

namespace App\Tests\Data;

use App\Data\Definitions\Fields\Field;
use App\Data\FieldValue;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Throwable;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @small
 */
class FieldValueTest extends TestCase
{
    /**
     * @dataProvider validateTypeDataProvider
     */
    public function testValidateType(Field $field, mixed $value, bool $isOk): void
    {
        if (!$isOk) {
            $this->expectException(InvalidArgumentException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        FieldValue::validateType($field, $value);
    }

    public function validateTypeDataProvider(): DataProvider
    {
        return DataProvider::tuples(
            [Field::WORKS_WITH_MINORS, null,  true], // null is considered empty for boolean
            [Field::WORKS_WITH_MINORS, false, true],
            [Field::WORKS_WITH_MINORS, '',    false],

            [Field::DATE_ADDED, null, true], // null is considered empty for dates
            [Field::DATE_ADDED, new DateTimeImmutable(), true],
            [Field::DATE_ADDED, '', false],

            [Field::FORMERLY, null,         false],
            [Field::FORMERLY, '',           false],
            [Field::FORMERLY, [],           true], // empty array is considered empty for string lists
            [Field::FORMERLY, ['a', 'b'],   true],
            [Field::FORMERLY, ['a', 0],     false],
            [Field::FORMERLY, ['a' => 'b'], false],
        );
    }

    /**
     * @dataProvider fromStringDataProvider
     *
     * @param Throwable|list<string>|string|bool $expected
     */
    public function testFromString(Field $field, string $value, Throwable|array|string|bool $expected): void
    {
        if ($expected instanceof Throwable) {
            $this->expectException($expected::class);
        }

        self::assertEquals($expected, FieldValue::fromString($field, $value));
    }

    public function fromStringDataProvider(): DataProvider
    {
        return DataProvider::tuples(
            [Field::NAME, '', ''],
            [Field::NAME, 'OK', 'OK'],

            [Field::WORKS_WITH_MINORS, '', new InvalidArgumentException()],
            [Field::WORKS_WITH_MINORS, 'Does not', new InvalidArgumentException()],
            [Field::WORKS_WITH_MINORS, 'True', true],
            [Field::WORKS_WITH_MINORS, 'false', false],

            [Field::FORMERLY, '',             []],
            [Field::FORMERLY, 'item1',        ['item1']],
            [Field::FORMERLY, "item1\nitem2", ['item1', 'item2']],
        );
    }
}
