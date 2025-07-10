<?php

declare(strict_types=1);

namespace App\Tests\Data;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Data\FieldValue;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Throwable;

#[Small]
class FieldValueTest extends TestCase
{
    #[DataProvider('validateTypeDataProvider')]
    public function testValidateType(Field $field, mixed $value, bool $isOk): void
    {
        if (!$isOk) {
            $this->expectException(InvalidArgumentException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        FieldValue::validateType($field, $value);
    }

    /**
     * @return list<array{Field, mixed, bool}>
     */
    public static function validateTypeDataProvider(): array
    {
        $date = new DateTimeImmutable();

        return [
            [Field::NAME, null,     false],
            [Field::NAME, '',       true],
            [Field::NAME, 'Name',   true],
            [Field::NAME, [],       false],
            [Field::NAME, ['Name'], false],

            [Field::FORMERLY, null,         false],
            [Field::FORMERLY, true,         false],
            [Field::FORMERLY, '',           false],
            [Field::FORMERLY, [],           true],
            [Field::FORMERLY, ['a', 'b'],   true],
            [Field::FORMERLY, ['a', 0],     false],
            [Field::FORMERLY, ['a' => 'b'], false],

            [Field::WORKS_WITH_MINORS, null,   true],
            [Field::WORKS_WITH_MINORS, false,  true],
            [Field::WORKS_WITH_MINORS, '',     false],
            [Field::WORKS_WITH_MINORS, [],     false],
            [Field::WORKS_WITH_MINORS, [true], false],

            [Field::DATE_UPDATED, null,    true],
            [Field::DATE_UPDATED, $date,   true],
            [Field::DATE_UPDATED, '',      false],
            [Field::DATE_UPDATED, [],      false],
            [Field::DATE_UPDATED, [$date], false],

            [Field::AGES, null,                true],
            [Field::AGES, '',                  false],
            [Field::AGES, Ages::ADULTS,        true],
            [Field::AGES, Ages::ADULTS->value, false],
            [Field::AGES, [Ages::ADULTS],      false],

            [Field::CONTACT_ALLOWED, null,                     true],
            [Field::CONTACT_ALLOWED, '',                       false],
            [Field::CONTACT_ALLOWED, ContactPermit::NO,        true],
            [Field::CONTACT_ALLOWED, ContactPermit::NO->value, false],
            [Field::CONTACT_ALLOWED, [ContactPermit::NO],      false],
        ];
    }

    /**
     * @param Throwable|list<string>|string|bool $expected
     */
    #[DataProvider('fromStringDataProvider')]
    public function testFromString(Field $field, string $value, Throwable|array|string|bool $expected): void
    {
        if ($expected instanceof Throwable) {
            $this->expectException($expected::class);
        }

        self::assertEquals($expected, FieldValue::fromString($field, $value));
    }

    /**
     * @return list<array{Field, string, Throwable|list<string>|string|bool}>
     */
    public static function fromStringDataProvider(): array
    {
        $iae = new InvalidArgumentException();

        return [
            [Field::NAME, '', ''],
            [Field::NAME, 'OK', 'OK'],

            [Field::FORMERLY, '',             []],
            [Field::FORMERLY, 'item1',        ['item1']],
            [Field::FORMERLY, "item1\nitem2", ['item1', 'item2']],

            [Field::WORKS_WITH_MINORS, '',         $iae],
            [Field::WORKS_WITH_MINORS, 'Does not', $iae],
            [Field::WORKS_WITH_MINORS, 'True',     true],
            [Field::WORKS_WITH_MINORS, 'false',    false],

            [Field::DATE_UPDATED, '', $iae], // Not supported yet
            [Field::AGES, '', $iae], // Not supported yet
            [Field::CONTACT_ALLOWED, '', $iae], // Not supported yet
        ];
    }

    #[DataProvider('isProvidedDataProvider')]
    public function testIsProvided(Field $field, mixed $value, Throwable|bool $expected): void
    {
        if ($expected instanceof Throwable) {
            $this->expectException($expected::class);
        }

        self::assertEquals($expected, FieldValue::isProvided($field, $value));
    }

    /**
     * @return list<array{Field, mixed, Throwable|bool}>
     */
    public static function isProvidedDataProvider(): array
    {
        $iae = new InvalidArgumentException();
        $date = new DateTimeImmutable();

        return [
            [Field::NAME, null,     $iae],
            [Field::NAME, '',       false],
            [Field::NAME, 'Name',   true],
            [Field::NAME, [],       $iae],
            [Field::NAME, ['Name'], $iae],

            [Field::FORMERLY, null,         $iae],
            [Field::FORMERLY, '',           $iae],
            [Field::FORMERLY, 'Formerly',   $iae],
            [Field::FORMERLY, [],           false],
            [Field::FORMERLY, ['Formerly'], true],

            [Field::DATE_UPDATED, null,    false],
            [Field::DATE_UPDATED, $date,   true],
            [Field::DATE_UPDATED, '',      $iae],
            [Field::DATE_UPDATED, [],      $iae],
            [Field::DATE_UPDATED, [$date], $iae],

            [Field::WORKS_WITH_MINORS, null,   false],
            [Field::WORKS_WITH_MINORS, true,   true],
            [Field::WORKS_WITH_MINORS, false,  true],
            [Field::WORKS_WITH_MINORS, 'True', $iae],
            [Field::WORKS_WITH_MINORS, [],     $iae],
            [Field::WORKS_WITH_MINORS, [true], $iae],

            [Field::AGES, null,                false],
            [Field::AGES, Ages::ADULTS,        true],
            [Field::AGES, Ages::ADULTS->value, $iae],
            [Field::AGES, [Ages::ADULTS],      $iae],

            [Field::CONTACT_ALLOWED, null,                     false],
            [Field::CONTACT_ALLOWED, ContactPermit::NO,        true],
            [Field::CONTACT_ALLOWED, ContactPermit::NO->value, $iae],
            [Field::CONTACT_ALLOWED, [ContactPermit::NO],      $iae],
        ];
    }
}
