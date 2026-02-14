<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Data\Definitions\Fields\Field;
use App\Entity\CreatorValue;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class CreatorValueRepositoryTest extends FuzzrakeKernelTestCase
{
    public function testGetDistinctValues(): void
    {
        self::persistAndFlush(
            new Creator()->setFeatures(['AB', 'CD'])->setOtherFeatures(['01', '02']),
            new Creator()->setFeatures(['EF'])->setOtherFeatures(['03', '04']),
            new Creator()->setFeatures(['GH'])->setInactiveReason('Inactive'), // Counts
        );

        $subject = self::getEM()->getRepository(CreatorValue::class);

        $result = $subject->getDistinctValues(Field::FEATURES->value);

        self::assertEquals(['AB', 'CD', 'EF', 'GH'], $result->getValuesArray());
    }

    public function testCountDistinctInActiveCreatorsHaving(): void
    {
        self::persistAndFlush(
            new Creator()->setFeatures(['AB', 'CD']),
            new Creator()->setFeatures(['AB']),
            new Creator()->setOtherFeatures(['EF']), // Not features
            new Creator()->setFeatures(['GH'])->setInactiveReason('Inactive'), // Counts
        );

        $subject = self::getEM()->getRepository(CreatorValue::class);

        $result = $subject->countDistinctInActiveCreatorsHaving(Field::FEATURES->value);

        self::assertEquals([
            'AB' => 2,
            'CD' => 1,
        ], $result->toArray());
    }

    public function testCountActiveCreatorsHavingAnyOf(): void
    {
        self::persistAndFlush(
            new Creator()->setFeatures(['AB', 'CD']),
            new Creator()->setFeatures(['EF']),
            new Creator()->setFeatures(['GH', 'IJ'])->setInactiveReason('Inactive'), // Doesn't count
            new Creator()->setOtherFeatures(['KL', 'MN']),
            new Creator()->setStyles(['OP']), // Not (other) features
            new Creator()->setOrderTypes(['QR']), // Not (other) features
        );

        $subject = self::getEM()->getRepository(CreatorValue::class);

        $result = $subject->countActiveCreatorsHavingAnyOf([Field::FEATURES->value, Field::OTHER_FEATURES->value]);

        self::assertSame(3, $result);
    }
}
