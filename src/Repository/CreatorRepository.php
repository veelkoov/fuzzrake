<?php

declare(strict_types=1);

namespace App\Repository;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\NewCreator;
use App\Entity\Creator;
use App\Utils\Creator\CreatorId;
use App\Utils\Creator\CreatorList;
use App\Utils\Creator\SmartAccessDecorator as CreatorSAD;
use App\Utils\Exceptions\UnbelievableRuntimeException;
use App\Utils\Pagination\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\OffsetPaginator;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;
use Generator;
use SortDirection;
use Veelkoov\Debris\Maps\NullBoolToInt;
use Veelkoov\Debris\Maps\StringToInt;
use Veelkoov\Debris\Sets\StringSet;
use Veelkoov\Debris\Strings;
use Veelkoov\Debris\Vecs\IntVec;

/**
 * @extends ServiceEntityRepository<Creator>
 */
class CreatorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Creator::class);
    }

    public function add(Creator|CreatorSAD $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Creator|CreatorSAD $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Generator<Creator>
     */
    public function getAllPaged(): Generator
    {
        $query = $this->getCreatorsQueryBuilder()->getQuery();
        $window = Pagination::getWindow(1);

        while (true) {
            $page = new OffsetPaginator()->paginate($query, $window);

            foreach ($page as $creator) {
                yield $creator; // @phpstan-ignore generator.valueType
            }

            if (!$page->hasNextPage()) {
                return;
            }

            $window = $page->getNextWindow();
        }
    }

    public function getCompleteById(int $id): Creator
    {
        try {
            // @phpstan-ignore return.type (I don't know how to fix that)
            return $this->getCreatorsQueryBuilder()
                ->andWhere('d_c.id = :id')
                ->setParameter('id', $id)
                ->addOrderBy('d_cu.id') // Keep miniatures in the same order on creator cards; grep-code-order-support-workaround
                ->getQuery()
                ->getSingleResult();
        } catch (NonUniqueResultException $exception) {
            throw new UnbelievableRuntimeException($exception);
        }
    }

    private function getCreatorsQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('d_c')
            ->leftJoin('d_c.volatileData', 'd_cvd')->addSelect('d_cvd')
            ->leftJoin('d_c.urls', 'd_cu')->addSelect('d_cu')
            ->leftJoin('d_cu.state', 'd_cus')->addSelect('d_cus')
            ->leftJoin('d_c.offerStatuses', 'd_cos')->addSelect('d_cos')
            ->leftJoin('d_c.creatorIds', 'd_ci')->addSelect('d_ci')
            ->leftJoin('d_c.values', 'd_cv')->addSelect('d_cv')
            ->leftJoin('d_c.labels', 'd_cl')->addSelect('d_cl')
        ;
    }

    /**
     * @throws UnexpectedResultException
     */
    public function getDistinctCountriesCount(): int
    {
        $resultData = $this->createQueryBuilder('d_c')
            ->select('COUNT (DISTINCT d_c.country)')
            ->where('d_c.country != \'\'')
            ->andWhere('d_c.country != \'EU\'') // grep-country-eu
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $resultData;
    }

    public function getDistinctCountries(): StringSet
    {
        $result = $this->createQueryBuilder('d_c')
            ->select('DISTINCT d_c.country')
            ->getQuery()
            ->getSingleColumnResult();

        return new StringSet($result); // @phpstan-ignore argument.type (Lack of skill to fix this)
    }

    public function getDistinctStates(): StringSet
    {
        $result = $this->createQueryBuilder('d_c')
            ->select('DISTINCT d_c.state')
            ->getQuery()
            ->getSingleColumnResult();

        return new StringSet($result); // @phpstan-ignore argument.type (Lack of skill to fix this)
    }

    public function getActiveOffersPaymentPlansStats(): NullBoolToInt
    {
        $result = $this->createQueryBuilder('d_c')
            ->select('d_c.offersPaymentPlans AS offers', 'COUNT(d_c) AS count')
            ->where('d_c.inactiveReason = :empty')
            ->groupBy('d_c.offersPaymentPlans')
            ->setParameter('empty', '')
            ->getQuery()
            ->getArrayResult();

        return NullBoolToInt::fromRows($result, 'offers', 'count');
    }

    /**
     * @param literal-string $columnName
     */
    public function countDistinctInActiveCreators(string $columnName): StringToInt
    {
        $result = $this->getEntityManager()->createQuery("
            SELECT d_c.$columnName AS value, COUNT(d_c.$columnName) AS count
            FROM \\App\\Entity\\Creator AS d_c
            WHERE d_c.inactiveReason = :empty
            GROUP BY d_c.$columnName
            ORDER BY count
        ")
            ->setParameter('empty', '')
            ->getArrayResult();

        return StringToInt::fromRows($result, 'value', 'count');
    }

    /**
     * @return Creator[]
     */
    public function findNamedSimilarly(Strings $names): array
    {
        $builder = $this->createQueryBuilder('d_c')
            ->leftJoin('d_c.creatorIds', 'd_ci');

        $i = 0;

        foreach ($names as $name) {
            $builder->orWhere("d_c.name = :eq$i OR (d_c.formerly <> '' AND d_c.formerly LIKE :like$i)");
            $builder->setParameter("eq$i", $name);
            $builder->setParameter("like$i", "%$name%");
            ++$i;
        }

        return $builder->getQuery()->getResult();
    }

    /**
     * @throws NoResultException
     */
    public function findByCreatorId(string $creatorId): Creator
    {
        if (!CreatorId::isValid($creatorId)) {
            throw new NoResultException();
        }

        try {
            $resultData = $this->createQueryBuilder('d_c')
                ->join('d_c.creatorIds', 'd_ci')
                ->where('d_ci.creatorId = :creatorId')
                ->setParameter('creatorId', $creatorId)

                // Keep photos in the same order in the I/U form; grep-code-order-support-workaround
                ->leftJoin('d_c.urls', 'd_cu')
                ->orderBy('d_cu.id')

                ->getQuery()
                ->getSingleResult();
        } catch (NonUniqueResultException $e) { // @codeCoverageIgnoreStart
            throw new UnbelievableRuntimeException($e);
        } // @codeCoverageIgnoreEnd

        return $resultData; // @phpstan-ignore return.type (Lack of skill to fix this)
    }

    public function countActive(): int
    {
        $resultData = $this->createQueryBuilder('d_c')
            ->select('COUNT(d_c)')
            ->where('d_c.inactiveReason = :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $resultData;
    }

    /**
     * @throws UnexpectedResultException
     */
    public function countAll(): int
    {
        $resultData = $this->createQueryBuilder('d_c')
            ->select('COUNT(d_c)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $resultData;
    }

    /**
     * @throws UnexpectedResultException
     */
    public function getCsTrackedCount(): int
    {
        $resultData = $this->createQueryBuilder('d_c')
            ->leftJoin('d_c.urls', 'd_cu')
            ->select('COUNT(DISTINCT d_c.id)')
            ->where('d_cu.type = :type')
            ->andWhere('d_c.inactiveReason = :empty')
            ->setParameter('type', Field::URL_COMMISSIONS->value)
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $resultData;
    }

    public function getWithIds(IntVec $idsOfCreators): CreatorList
    {
        $entities = $this->getCreatorsQueryBuilder()
            ->where('d_c.id IN (:idsOfCreators)')
            ->setParameter('idsOfCreators', $idsOfCreators)
            ->getQuery()
            ->getResult();

        return CreatorList::wrap($entities);
    }

    public function getIds(bool $includeInactive): IntVec
    {
        $queryBuilder = $this->createQueryBuilder('d_c')->select('d_c.id');

        if (!$includeInactive) {
            $queryBuilder->where('d_c.inactiveReason = :empty')->setParameter('empty', '');
        }

        // @phpstan-ignore argument.type (I don't know how to fix that)
        return new IntVec($queryBuilder->getQuery()->getSingleColumnResult());
    }

    public function getNewIds(): IntVec
    {
        $queryBuilder = $this->createQueryBuilder('d_c')
            ->select('d_c.id')
            ->join('d_c.values', 'd_cv')
            ->where('d_cv.fieldName = :fieldName', 'd_cv.value > :value')
            ->setParameter('fieldName', Field::DATE_ADDED->value)
            ->setParameter('value', NewCreator::getCutoffDateStr())
            ->orderBy('d_cv.value', SortDirection::Descending)
        ;

        // @phpstan-ignore argument.type (I don't know how to fix that)
        return new IntVec($queryBuilder->getQuery()->getSingleColumnResult());
    }
}
