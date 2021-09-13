<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Artisan as ArtisanE;
use App\Tests\TestUtils\DbEnabledKernelTestCase;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;

class ArtisanRepositoryTest extends DbEnabledKernelTestCase
{
    /**
     * @dataProvider findByMakerIdDataProvider
     *
     * @param ArtisanE[] $artisans
     *
     * @throws ORMException
     */
    public function testFindByMakerId(array $artisans, string $makerId, ?int $resultIdx): void
    {
        self::bootKernel();

        foreach ($artisans as $key => $artisan) {
            $artisans[$key] = clone $artisan; // Don't mangle the tests
            self::getEM()->persist($artisans[$key]);
        }
        self::flush();

        if (null === $resultIdx) {
            $this->expectException(NoResultException::class);
        }

        $result = self::getEM()->getRepository(ArtisanE::class)->findByMakerId($makerId);

        static::assertEquals($artisans[$resultIdx], $result);
    }

    public function findByMakerIdDataProvider(): array
    {
        Artisan::wrap($m1 = new ArtisanE())->setMakerId('MAKER11');
        Artisan::wrap($m2 = new ArtisanE())->setMakerId('MAKER21')->setFormerMakerIds('MAKER22');
        Artisan::wrap($m3 = new ArtisanE())->setMakerId('MAKER31')->setFormerMakerIds("MAKER32\nMAKER33");

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
     */
    public function testFindByMakerIdReturnsCompleteMakerIdsSet(): void
    {
        self::bootKernel();

        $accessor = Artisan::wrap($artisan = new ArtisanE())->setMakerId('MAKRID1')->setFormerMakerIds("MAKRID2\nMAKRID3");

        self::persistAndFlush($artisan);
        self::clear();

        $retrieved1 = self::getEM()->getRepository(ArtisanE::class)->findByMakerId('MAKRID1');

        self::assertEquals($artisan->getMakerId(), $retrieved1->getMakerId());
        self::assertEquals($accessor->getFormerMakerIds(), Artisan::wrap($retrieved1)->getFormerMakerIds());

        $retrieved2 = self::getEM()->getRepository(ArtisanE::class)->findByMakerId('MAKRID2');
        self::assertEquals($retrieved1, $retrieved2);
    }

    public function testFindBestMatches(): void
    {
        self::bootKernel();

        Artisan::wrap($a1 = new ArtisanE())
            ->setName('Maker 1')
            ->setFormerly("Old maker A\nOlder maker A")
            ->setMakerId('MAKER11')
            ->setFormerMakerIds('MAKER12');
        Artisan::wrap($a2 = new ArtisanE())
            ->setName('Maker 2')
            ->setFormerly("Old maker B\nmaker A")
            ->setMakerId('MAKER21')
            ->setFormerMakerIds("MAKER22\nMAKER23");

        self::persistAndFlush($a1, $a2);

        $repo = self::getArtisanRepository();

        self::assertEquals([$a1], $repo->findBestMatches(['Maker 1'], ['MAKER12'], null));
        self::assertEquals([$a1], $repo->findBestMatches(['Old maker A'], ['NEWMKID'], null));
        self::assertEquals([$a2], $repo->findBestMatches(['Anything'], [], 'Old maker B'));
        self::assertEquals([$a2], $repo->findBestMatches([], ['MAKER23'], null));
    }
}
