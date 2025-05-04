<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Data\Definitions\Fields\Field;
use App\Entity\CreatorValue;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class CreatorValueRepositoryWithEMTest extends FuzzrakeKernelTestCase
{
    public function testGetDistinctValues(): void
    {
        self::persistAndFlush(
            Creator::new()->setFeatures(['AB', 'CD'])->setOtherFeatures(['01', '02']),
            Creator::new()->setFeatures(['EF'])->setOtherFeatures(['03', '04']),
            Creator::new()->setFeatures(['GH'])->setInactiveReason('Inactive'), // Counts
        );

        $subject = self::getEM()->getRepository(CreatorValue::class);

        $result = $subject->getDistinctValues(Field::FEATURES->value);

        self::assertEquals(['AB', 'CD', 'EF', 'GH'], $result->getValuesArray());
    }

    public function testCountDistinctInActiveCreatorsHaving(): void
    {
        self::persistAndFlush(
            Creator::new()->setFeatures(['AB', 'CD']),
            Creator::new()->setFeatures(['AB']),
            Creator::new()->setOtherFeatures(['EF']), // Not features
            Creator::new()->setFeatures(['GH'])->setInactiveReason('Inactive'), // Counts
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
            Creator::new()->setFeatures(['AB', 'CD']),
            Creator::new()->setFeatures(['EF']),
            Creator::new()->setFeatures(['GH', 'IJ'])->setInactiveReason('Inactive'), // Doesn't count
            Creator::new()->setOtherFeatures(['KL', 'MN']),
            Creator::new()->setStyles(['OP']), // Not (other) features
            Creator::new()->setOrderTypes(['QR']), // Not (other) features
        );

        $subject = self::getEM()->getRepository(CreatorValue::class);

        $result = $subject->countActiveCreatorsHavingAnyOf([Field::FEATURES->value, Field::OTHER_FEATURES->value]);

        self::assertSame(3, $result);
    }
}
