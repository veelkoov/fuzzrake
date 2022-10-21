<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Artisan as ArtisanE;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NoResultException;

/**
 * @medium
 */
class ArtisanRepositoryWithEMTest extends KernelTestCaseWithEM
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

        $result = self::getArtisanRepository()->findByMakerId($makerId);

        static::assertEquals($artisans[$resultIdx], $result);
    }

    public function findByMakerIdDataProvider(): array // @phpstan-ignore-line
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

        $retrieved1 = self::getArtisanRepository()->findByMakerId('MAKRID1');

        self::assertEquals($artisan->getMakerId(), $retrieved1->getMakerId());
        self::assertEquals($accessor->getFormerMakerIds(), Artisan::wrap($retrieved1)->getFormerMakerIds());

        $retrieved2 = self::getArtisanRepository()->findByMakerId('MAKRID2');
        self::assertEquals($retrieved1, $retrieved2);
    }

    public function testFindBestMatches(): void
    {
        self::bootKernel();

        $commonPart = 'maker A';

        $m1name = 'Maker 1';
        $m1oldName1 = "Old $commonPart";
        $m1oldName2 = "Older $commonPart";
        $m1makerId = 'MAKER11';
        $m1oldMakerId1 = 'MAKER12';

        Artisan::wrap($a1 = new ArtisanE())
            ->setName($m1name)
            ->setFormerly("{$m1oldName1}\n{$m1oldName2}")
            ->setMakerId($m1makerId)
            ->setFormerMakerIds($m1oldMakerId1);

        $m2name = 'Maker 2';
        $m2oldName1 = 'Old maker B';
        $m2oldName2 = $commonPart;
        $m2makerId = 'MAKER21';
        $m2oldMakerId1 = 'MAKER22';
        $m2OldMakerId2 = 'MAKER23';

        Artisan::wrap($a2 = new ArtisanE())
            ->setName($m2name)
            ->setFormerly("{$m2oldName1}\n{$m2oldName2}")
            ->setMakerId($m2makerId)
            ->setFormerMakerIds($m2oldMakerId1."\n".$m2OldMakerId2);

        self::persistAndFlush($a1, $a2);

        $repo = self::getArtisanRepository();

        self::assertEquals([$a1], $repo->findBestMatches([$m1name], [$m1oldMakerId1]));
        self::assertEquals([$a1], $repo->findBestMatches([$m1oldName1], ['NEWMKID']));
        self::assertEquals([$a2], $repo->findBestMatches([], [$m2OldMakerId2]));
        self::assertEquals([$a1, $a2], $repo->findBestMatches([$m2oldName2], [])); // Shares common part
        self::assertEquals([$a1, $a2], $repo->findBestMatches([], [$m1makerId, $m2oldMakerId1]));
    }
}
