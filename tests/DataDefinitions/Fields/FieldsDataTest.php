<?php

declare(strict_types=1);

namespace App\Tests\DataDefinitions\Fields;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\FieldsData;
use PHPUnit\Framework\TestCase;

class FieldsDataTest extends TestCase
{
    public function testSanityChecks(): void
    {
        self::assertCount(count(Field::cases()), FieldsData::DATA);

        foreach (Field::cases() as $field) {
            $data = FieldsData::DATA[$field->name][0];

            self::assertContains($data[0], [' ', FieldsData::MARK_LIST]);
            self::assertEquals(' ', $data[1]);
            self::assertContains($data[2], [' ', FieldsData::MARK_FREE_FORM]);
            self::assertEquals(' ', $data[3]);
            self::assertContains($data[4], [' ', FieldsData::MARK_STATS]);
            self::assertEquals(' ', $data[5]);
            self::assertContains($data[6], [' ', FieldsData::MARK_PUBLIC]);
            self::assertEquals(' ', $data[7]);
            self::assertContains($data[8], [' ', FieldsData::MARK_IN_IU_FORM]);
            self::assertEquals(' ', $data[9]);
        }
    }
}
