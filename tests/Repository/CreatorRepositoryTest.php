<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Tests\TestUtils\UserCreator;
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
     * @param Creator[] $creators
     *
     * @throws ORMException
     */
    #[DataProvider('findByCreatorIdDataProvider')]
    public function testFindByCreatorId(array $creators, string $creatorId, ?int $resultIdx): void
    {
        foreach ($creators as $creator) {
            self::persist($creator, $creator->entity->getUser());
        }
        self::flush();

        if (null === $resultIdx) {
            $this->expectException(NoResultException::class);
        }

        $result = self::getCreatorRepository()->findByCreatorId($creatorId);

        if (null !== $resultIdx) {
            self::assertSame($creators[$resultIdx]->entity, $result);
        }
    }

    /**
     * @return list<array{list<Creator>, string, ?int}>
     */
    public static function findByCreatorIdDataProvider(): array
    {
        $m1 = fn () => UserCreator::get()->setCreatorId('TESTI11');
        $m2 = fn () => UserCreator::get()->setCreatorId('TESTI21')->setFormerCreatorIds(['TESTI22']);
        $m3 = fn () => UserCreator::get()->setCreatorId('TESTI31')->setFormerCreatorIds(['TESTI32', 'TESTI33']);

        return [
            [[$m1()], 'TESTI11', 0],
            [[$m1()], 'TESTI12', null],
            [[$m1()], 'TESTI',   null],

            [[$m2()], 'TESTI21', 0],
            [[$m2()], 'TESTI22', 0],
            [[$m2()], 'TESTI',   null],

            [[$m1(), $m2()], 'TESTI',   null],
            [[$m1(), $m2()], 'TESTI11', 0],
            [[$m1(), $m2()], 'TESTI21', 1],
            [[$m1(), $m2()], 'TESTI22', 1],

            [[$m3()], 'TESTI30',   null],
            [[$m3()], 'TESTI31',   0],
            [[$m3()], 'TESTI32',   0],
            [[$m3()], 'TESTI33',   0],
            [[$m3()], "MER2\nFOR", null],
        ];
    }

    /**
     * @throws NoResultException
     */
    public function testFindByCreatorIdReturnsCompleteCreatorIdsSet(): void
    {
        $creator = UserCreator::get()->setCreatorId('TESTID1')->setFormerCreatorIds(['TESTID2', 'TESTID3']);

        self::persistAndFlushWithUsers($creator);
        self::clear();

        $retrieved1 = self::getCreatorRepository()->findByCreatorId('TESTID1');

        self::assertNotSame($creator->entity, $retrieved1);
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

        $creator1 = UserCreator::get()
            ->setName($creator1name)
            ->setFormerly([$creator1oldName1, $creator1oldName2]);

        $creator2name = 'Creator 2';
        $creator2oldName1 = 'Old creator B';
        $creator2oldName2 = $commonPart;

        $creator2 = UserCreator::get()
            ->setName($creator2name)
            ->setFormerly([$creator2oldName1, $creator2oldName2]);

        self::persistAndFlushWithUsers($creator1, $creator2);

        $repo = self::getCreatorRepository();

        self::assertEquals([$creator1->entity], $repo->findNamedSimilarly(StringList::of($creator1name)));
        self::assertEquals([$creator1->entity], $repo->findNamedSimilarly(StringList::of($creator1oldName1)));
        self::assertEquals([$creator1->entity, $creator2->entity], $repo->findNamedSimilarly(StringList::of($creator2oldName2))); // Shares common part
    }

    public function testFindByCreatorIds(): void
    {
        $creator1creatorId = 'C01ID01';
        $creator1oldCreatorId1 = 'C01ID02';

        $creator1 = UserCreator::get()
            ->setCreatorId($creator1creatorId)
            ->setFormerCreatorIds([$creator1oldCreatorId1]);

        $creator2creatorId = 'C02ID01';
        $creator2oldCreatorId1 = 'C02ID02';
        $creator2oldCreatorId2 = 'C02ID03';

        $creator2 = UserCreator::get()
            ->setCreatorId($creator2creatorId)
            ->setFormerCreatorIds([$creator2oldCreatorId1, $creator2oldCreatorId2]);

        self::persistAndFlushWithUsers($creator1, $creator2);

        $repo = self::getCreatorRepository();

        self::assertEquals([$creator1->entity], $repo->findByCreatorIds([$creator1creatorId]));
        self::assertEquals([$creator1->entity], $repo->findByCreatorIds([$creator1oldCreatorId1]));
        self::assertEquals([], $repo->findByCreatorIds(['NEWCRID']));
        self::assertEquals([$creator2->entity], $repo->findByCreatorIds([$creator2oldCreatorId2]));
        self::assertEquals([$creator1->entity, $creator2->entity], $repo->findByCreatorIds([$creator1creatorId, $creator2oldCreatorId1]));
    }
}
