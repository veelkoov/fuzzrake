<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Artisan;
use App\Tests\Controller\DbEnabledWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;

class ArtisanRepositoryTest extends DbEnabledWebTestCase
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
        self::setUpDb();

        foreach ($artisans as $key => $_) {
            $artisans[$key] = clone $artisans[$key]; // Don't mangle the tests
            self::$entityManager->persist($artisans[$key]);
        }
        self::$entityManager->flush();

        if (null === $resultIdx) {
            $this->expectException(NoResultException::class);
        } elseif (-1 === $resultIdx) {
            $this->expectException(NonUniqueResultException::class);
        }

        $result = self::$entityManager->getRepository(Artisan::class)->findByMakerId($makerId);

        static::assertEquals($artisans[$resultIdx], $result);
    }

    public function findByMakerIdDataProvider(): array
    {
        $m1 = (new Artisan())->setMakerId('MAKERI1');
        $m2 = (new Artisan())->setMakerId('MAKERI2')->setFormerMakerIds('MAKERI1');
        $m3 = (new Artisan())->setMakerId('MAKERI3')->setFormerMakerIds("FORMER2\nFORMER3\nFORMER4");

        return [
            [[$m1], 'MAKERI1', 0],
            [[$m1], 'MAKERI2', null],
            [[$m1], 'MAKER',   null],

            [[$m2], 'MAKERI1', 0],
            [[$m2], 'MAKERI2', 0],
            [[$m2], 'MAKER',   null],

            [[$m1, $m2], 'MAKER',   null],
            [[$m1, $m2], 'MAKERI1', -1],
            [[$m1, $m2], 'MAKERI2', 1],

            [[$m3], 'FORMER',    null],
            [[$m3], 'FORMER2',   0],
            [[$m3], 'FORMER3',   0],
            [[$m3], 'FORMER4',   0],
            [[$m3], "MER2\nFOR", null],
        ];
    }
}
