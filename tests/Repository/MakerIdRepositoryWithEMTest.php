<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\MakerId;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class MakerIdRepositoryWithEMTest extends KernelTestCaseWithEM
{
    public function testGetOldToNewMakerIdsMap(): void
    {
        self::bootKernel();

        $m1 = (new Artisan())->setMakerId('MAKER11');
        $m2 = (new Artisan())->setMakerId('MAKER21')->setFormerMakerIds('MAKER22');
        $m3 = (new Artisan())->setMakerId('MAKER31')->setFormerMakerIds("MAKER32\nMAKER33");
        $m4 = (new Artisan())->setFormerMakerIds('M000004');

        self::persistAndFlush($m1, $m2, $m3, $m4);

        $map = self::getEM()->getRepository(MakerId::class)->getOldToNewMakerIdsMap(); // @phpstan-ignore-line

        self::assertEquals([
            'MAKER22' => 'MAKER21',
            'MAKER32' => 'MAKER31',
            'MAKER33' => 'MAKER31',
        ], $map);
    }
}
