<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Artisan;
use App\Tests\TestUtils\DbEnabledKernelTestCase;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\Mapping\MappingException;

class ArtisanRepositoryTest extends DbEnabledKernelTestCase
{
    /**
     * @dataProvider findByMakerIdDataProvider
     *
     * @param Artisan[] $artisans
     *
     * @throws ORMException
     */
    public function testFindByMakerId(array $artisans, string $makerId, ?int $resultIdx): void
    {
        self::bootKernel();

        foreach ($artisans as $key => $_) {
            $artisans[$key] = clone $artisans[$key]; // Don't mangle the tests
            self::getEM()->persist($artisans[$key]);
        }
        self::getEM()->flush();

        if (null === $resultIdx) {
            $this->expectException(NoResultException::class);
        }

        $result = self::getEM()->getRepository(Artisan::class)->findByMakerId($makerId);

        static::assertEquals($artisans[$resultIdx], $result);
    }

    public function findByMakerIdDataProvider(): array
    {
        $m1 = (new Artisan())->setMakerId('MAKER11');
        $m2 = (new Artisan())->setMakerId('MAKER21')->setFormerMakerIds('MAKER22');
        $m3 = (new Artisan())->setMakerId('MAKER31')->setFormerMakerIds("MAKER32\nMAKER33");

        return [
            [[$m1], 'MAKER11', 0],
            [[$m1], 'MAKER12', null],
            [[$m1], 'MAKER',   null],

            [[$m2], 'MAKER21', 0],
            [[$m2], 'MAKER22', 0],
            [[$m2], 'MAKER',   null],

            [[$m1, $m2], 'MAKER',   null],
            [[$m1, $m2], 'MAKER11', 0],
            [[$m1, $m2], 'MAKER21', 1],
            [[$m1, $m2], 'MAKER22', 1],

            [[$m3], 'MAKER30',   null],
            [[$m3], 'MAKER31',   0],
            [[$m3], 'MAKER32',   0],
            [[$m3], 'MAKER33',   0],
            [[$m3], "MER2\nFOR", null],
        ];
    }

    /**
     * @throws NoResultException
     * @throws MappingException
     */
    public function testFindByMakerIdReturnsCompleteMakerIdsSet(): void
    {
        self::bootKernel();

        $artisan = (new Artisan())->setMakerId('MAKRID1')->setFormerMakerIds("MAKRID2\nMAKRID3");

        self::persistAndFlush($artisan);
        self::getEM()->clear();

        $retrieved1 = self::getEM()->getRepository(Artisan::class)->findByMakerId('MAKRID1');

        self::assertEquals($artisan->getMakerId(), $retrieved1->getMakerId());
        self::assertEquals($artisan->getFormerMakerIds(), $retrieved1->getFormerMakerIds());

        $retrieved2 = self::getEM()->getRepository(Artisan::class)->findByMakerId('MAKRID2');
        self::assertEquals($retrieved1, $retrieved2);
    }
}
