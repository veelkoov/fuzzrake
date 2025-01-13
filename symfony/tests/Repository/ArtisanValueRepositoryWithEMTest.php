<?php

namespace App\Tests\Repository;

use App\Data\Definitions\Fields\Field;
use App\Entity\ArtisanValue;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Creator;

/**
 * @medium
 */
class ArtisanValueRepositoryWithEMTest extends KernelTestCaseWithEM
{
    public function testGetDistinctValues(): void
    {
        self::bootKernel();

        self::persistAndFlush(
            Creator::new()->setFeatures(['AB', 'CD'])->setOtherFeatures(['01', '02']),
            Creator::new()->setFeatures(['EF'])->setOtherFeatures(['03', '04']),
            Creator::new()->setFeatures(['GH'])->setInactiveReason('Inactive'), // Counts
        );

        $subject = self::getEM()->getRepository(ArtisanValue::class);

        $result = $subject->getDistinctValues(Field::FEATURES->value);

        self::assertEquals(['AB', 'CD', 'EF', 'GH'], $result);
    }

    public function testCountDistinctInActiveCreatorsHaving(): void
    {
        self::bootKernel();

        self::persistAndFlush(
            Creator::new()->setFeatures(['AB', 'CD']),
            Creator::new()->setFeatures(['AB']),
            Creator::new()->setOtherFeatures(['EF']), // Not features
            Creator::new()->setFeatures(['GH'])->setInactiveReason('Inactive'), // Counts
        );

        $subject = self::getEM()->getRepository(ArtisanValue::class);

        $result = $subject->countDistinctInActiveCreatorsHaving(Field::FEATURES->value);

        self::assertEquals([
            'AB' => 2,
            'CD' => 1,
        ], $result);
    }

    public function testCountActiveCreatorsHavingAnyOf(): void
    {
        self::bootKernel();

        self::persistAndFlush(
            Creator::new()->setFeatures(['AB', 'CD']),
            Creator::new()->setFeatures(['EF']),
            Creator::new()->setFeatures(['GH', 'IJ'])->setInactiveReason('Inactive'), // Doesn't count
            Creator::new()->setOtherFeatures(['KL', 'MN']),
            Creator::new()->setStyles(['OP']), // Not (other) features
            Creator::new()->setOrderTypes(['QR']), // Not (other) features
        );

        $subject = self::getEM()->getRepository(ArtisanValue::class);

        $result = $subject->countActiveCreatorsHavingAnyOf([Field::FEATURES->value, Field::OTHER_FEATURES->value]);

        self::assertEquals(3, $result);
    }
}
