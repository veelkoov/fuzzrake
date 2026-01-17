<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Creator as CreatorE;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NoResultException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use Veelkoov\Debris\Lists\StringList;

#[Medium]
class CreatorRepositoryTest extends FuzzrakeKernelTestCase
{
    /**
     * @param CreatorE[] $creators
     *
     * @throws ORMException
     */
    #[DataProvider('findByCreatorIdDataProvider')]
    public function testFindByCreatorId(array $creators, string $creatorId, ?int $resultIdx): void
    {
        foreach ($creators as $key => $creator) {
            $creators[$key] = clone $creator; // Don't mangle the tests
            self::getEM()->persist($creators[$key]);
        }
        self::flush();

        if (null === $resultIdx) {
            $this->expectException(NoResultException::class);
        }

        $result = self::getCreatorRepository()->findByCreatorId($creatorId);

        static::assertEquals($creators[$resultIdx], $result);
    }

    /**
     * @return list<array{list<CreatorE>, string, ?int}>
     */
    public static function findByCreatorIdDataProvider(): array
    {
        $m1 = new CreatorE()->setCreatorId('TESTI11');
        $m2 = new CreatorE()->setCreatorId('TESTI21')->setFormerCreatorIds(['TESTI22']);
        $m3 = new CreatorE()->setCreatorId('TESTI31')->setFormerCreatorIds(['TESTI32', 'TESTI33']);

        return [
            [[$m1], 'TESTI11', 0],
            [[$m1], 'TESTI12', null],
            [[$m1], 'TESTI',   null],

            [[$m2], 'TESTI21', 0],
            [[$m2], 'TESTI22', 0],
            [[$m2], 'TESTI',   null],

            [[$m1, $m2], 'TESTI',   null],
            [[$m1, $m2], 'TESTI11', 0],
            [[$m1, $m2], 'TESTI21', 1],
            [[$m1, $m2], 'TESTI22', 1],

            [[$m3], 'TESTI30',   null],
            [[$m3], 'TESTI31',   0],
            [[$m3], 'TESTI32',   0],
            [[$m3], 'TESTI33',   0],
            [[$m3], "MER2\nFOR", null],
        ];
    }

    /**
     * @throws NoResultException
     */
    public function testFindByCreatorIdReturnsCompleteCreatorIdsSet(): void
    {
        $creator = new CreatorE()->setCreatorId('TESTID1')->setFormerCreatorIds(['TESTID2', 'TESTID3']);

        self::persistAndFlush($creator);
        self::clear();

        $retrieved1 = self::getCreatorRepository()->findByCreatorId('TESTID1');

        self::assertNotSame($creator, $retrieved1);
        self::assertSame($creator->getCreatorId(), $retrieved1->getCreatorId());
        self::assertEquals($creator->getFormerCreatorIds(), $retrieved1->getFormerCreatorIds());

        $retrieved2 = self::getCreatorRepository()->findByCreatorId('TESTID2');
        self::assertEquals($retrieved1, $retrieved2);
    }

    public function testFindNamedSimilarly(): void
    {
        $commonPart = 'creator A';

        $creator1name = 'Creator 1';
        $creator1oldName1 = "Old $commonPart";
        $creator1oldName2 = "Older $commonPart";

        Creator::wrap($creator1 = new CreatorE())
            ->setName($creator1name)
            ->setFormerly([$creator1oldName1, $creator1oldName2]);

        $creator2name = 'Creator 2';
        $creator2oldName1 = 'Old creator B';
        $creator2oldName2 = $commonPart;

        Creator::wrap($creator2 = new CreatorE())
            ->setName($creator2name)
            ->setFormerly([$creator2oldName1, $creator2oldName2]);

        self::persistAndFlush($creator1, $creator2);

        $repo = self::getCreatorRepository();

        self::assertEquals([$creator1], $repo->findNamedSimilarly(StringList::of($creator1name)));
        self::assertEquals([$creator1], $repo->findNamedSimilarly(StringList::of($creator1oldName1)));
        self::assertEquals([$creator1, $creator2], $repo->findNamedSimilarly(StringList::of($creator2oldName2))); // Shares common part
    }

    public function testFindByCreatorIds(): void
    {
        $creator1creatorId = 'C01ID01';
        $creator1oldCreatorId1 = 'C01ID02';

        Creator::wrap($creator1 = new CreatorE())
            ->setCreatorId($creator1creatorId)
            ->setFormerCreatorIds([$creator1oldCreatorId1]);

        $creator2creatorId = 'C02ID01';
        $creator2oldCreatorId1 = 'C02ID02';
        $creator2oldCreatorId2 = 'C02ID03';

        Creator::wrap($creator2 = new CreatorE())
            ->setCreatorId($creator2creatorId)
            ->setFormerCreatorIds([$creator2oldCreatorId1, $creator2oldCreatorId2]);

        self::persistAndFlush($creator1, $creator2);

        $repo = self::getCreatorRepository();

        self::assertEquals([$creator1], $repo->findByCreatorIds([$creator1creatorId]));
        self::assertEquals([$creator1], $repo->findByCreatorIds([$creator1oldCreatorId1]));
        self::assertEquals([], $repo->findByCreatorIds(['NEWCRID']));
        self::assertEquals([$creator2], $repo->findByCreatorIds([$creator2oldCreatorId2]));
        self::assertEquals([$creator1, $creator2], $repo->findByCreatorIds([$creator1creatorId, $creator2oldCreatorId1]));
    }
}
