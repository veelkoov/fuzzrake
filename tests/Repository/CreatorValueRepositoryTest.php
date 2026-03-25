<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Data\Definitions\Fields\Field;
use App\Entity\CreatorValue;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Tests\TestUtils\UserCreator;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class CreatorValueRepositoryTest extends FuzzrakeKernelTestCase
{
    public function testGetDistinctValues(): void
    {
        self::persistAndFlushWithUsers(
            UserCreator::get()->setFeatures(['AB', 'CD'])->setOtherFeatures(['01', '02']),
            UserCreator::get()->setFeatures(['EF'])->setOtherFeatures(['03', '04']),
            UserCreator::get()->setFeatures(['GH'])->setInactiveReason('Inactive'), // Counts
        );

        $subject = self::getEM()->getRepository(CreatorValue::class);

        $result = $subject->getDistinctValues(Field::FEATURES->value);

        self::assertEquals(['AB', 'CD', 'EF', 'GH'], $result->getValuesArray());
    }

    public function testCountDistinctInActiveCreatorsHaving(): void
    {
        self::persistAndFlushWithUsers(
            UserCreator::get()->setFeatures(['AB', 'CD']),
            UserCreator::get()->setFeatures(['AB']),
            UserCreator::get()->setOtherFeatures(['EF']), // Not features
            UserCreator::get()->setFeatures(['GH'])->setInactiveReason('Inactive'), // Counts
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
        self::persistAndFlushWithUsers(
            UserCreator::get()->setFeatures(['AB', 'CD']),
            UserCreator::get()->setFeatures(['EF']),
            UserCreator::get()->setFeatures(['GH', 'IJ'])->setInactiveReason('Inactive'), // Doesn't count
            UserCreator::get()->setOtherFeatures(['KL', 'MN']),
            UserCreator::get()->setStyles(['OP']), // Not (other) features
            UserCreator::get()->setOrderTypes(['QR']), // Not (other) features
        );

        $subject = self::getEM()->getRepository(CreatorValue::class);

        $result = $subject->countActiveCreatorsHavingAnyOf([Field::FEATURES->value, Field::OTHER_FEATURES->value]);

        self::assertSame(3, $result);
    }
}
