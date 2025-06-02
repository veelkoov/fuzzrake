<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Changes;

use App\Data\Definitions\Ages;
use App\Data\Definitions\Fields\Field;
use App\IuHandling\Changes\SimpleChange;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use TRegx\PhpUnit\DataProviders\DataProvider as TestDataProvider;

#[Small]
class SimpleChangeTest extends TestCase
{
    #[DataProvider('simpleChangeDataProvider')]
    public function testSimpleChange(Field $field,
        DateTimeImmutable|Ages|string|bool|null $old,
        DateTimeImmutable|Ages|string|bool|null $new,
        bool $changed, string $description): void
    {
        $subject = new SimpleChange($field, $old, $new);

        self::assertSame($changed, $subject->isActuallyAChange());
        self::assertSame($description, $subject->getDescription());
    }

    public static function simpleChangeDataProvider(): TestDataProvider
    {
        $date = new DateTimeImmutable('2024-05-19 20:46:00');
        $chdt = new DateTimeImmutable('2024-05-19 20:47:00');

        return TestDataProvider::tuples(
            [Field::NAME, 'Name', 'Name', false, 'NAME did not change'],
            [Field::NAME, '',     'Name', true,  'Added NAME: "Name"'],
            [Field::NAME, 'Name', '',     true,  'Removed NAME: "Name"'],
            [Field::NAME, 'Name', 'ChNa', true,  'Changed NAME from "Name" to "ChNa"'],

            [Field::WORKS_WITH_MINORS, true, true,  false, 'WORKS_WITH_MINORS did not change'],
            [Field::WORKS_WITH_MINORS, null, true,  true,  'Added WORKS_WITH_MINORS: "True"'],
            [Field::WORKS_WITH_MINORS, true, null,  true,  'Removed WORKS_WITH_MINORS: "True"'],
            [Field::WORKS_WITH_MINORS, true, false, true,  'Changed WORKS_WITH_MINORS from "True" to "False"'],

            [Field::DATE_UPDATED, $date, $date, false, 'DATE_UPDATED did not change'],
            [Field::DATE_UPDATED, null,  $date, true,  'Added DATE_UPDATED: "2024-05-19 20:46:00"'],
            [Field::DATE_UPDATED, $date, null,  true,  'Removed DATE_UPDATED: "2024-05-19 20:46:00"'],
            [Field::DATE_UPDATED, $date, $chdt, true,  'Changed DATE_UPDATED from "2024-05-19 20:46:00" to "2024-05-19 20:47:00"'],

            [Field::AGES, Ages::MIXED, Ages::MIXED,  false, 'AGES did not change'],
            [Field::AGES, null,        Ages::MIXED,  true,  'Added AGES: "MIXED"'],
            [Field::AGES, Ages::MIXED, null,         true,  'Removed AGES: "MIXED"'],
            [Field::AGES, Ages::MIXED, Ages::ADULTS, true,  'Changed AGES from "MIXED" to "ADULTS"'],
        );
    }
}
