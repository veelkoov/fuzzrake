<?php

namespace App\Tests\Data;

use App\Data\Definitions\Fields\Field;
use App\Data\FieldValue;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Throwable;
use TRegx\PhpUnit\DataProviders\DataProvider;

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
            [Field::WORKS_WITH_MINORS, null, true],
            [Field::WORKS_WITH_MINORS, false, true],
            [Field::WORKS_WITH_MINORS, '', false],

            [Field::DATE_ADDED, null, true],
            [Field::DATE_ADDED, new DateTimeImmutable(), true],
            [Field::DATE_ADDED, '', false],
        );
    }

    /**
     * @dataProvider fromStringDataProvider
     */
    public function testFromString(Field $field, string $value, Throwable|string|bool $expected): void
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
        );
    }
}
