<?php

declare(strict_types=1);

namespace App\Repository;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\NewCreator;
use App\Entity\Creator;
use App\Entity\CreatorValue;
use App\Filtering\RequestsHandling\QueryChoicesAppender;
use App\Utils\Creator\CreatorId;
use App\Utils\Creator\CreatorList;
use App\Utils\Creator\SmartAccessDecorator as CreatorSAD;
use App\Utils\Pagination\Pagination;
use App\Utils\UnbelievableRuntimeException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;
use Generator;
use Veelkoov\Debris\Collections\Strings;
use Veelkoov\Debris\Lists\IntList;
use Veelkoov\Debris\Lists\StringList;
use Veelkoov\Debris\Maps\StringToInt;
use Veelkoov\Debris\Maps\StringToString;
use Veelkoov\Debris\Sets\StringSet;

/**
 * @extends ServiceEntityRepository<Creator>
 *
 * @method Creator|null find($id, $lockMode = null, $lockVersion = null)
 * @method Creator|null findOneBy(array $criteria, array $orderBy = null)
 * @method Creator[]    findAll()
 * @method Creator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
     * @return Creator[]
     */
    public function getNewWithLimit(): array
    {
        return $this->getCreatorsQueryBuilder()
            ->where('d_cv.fieldName = :fieldName')
            ->andWhere('d_cv.value > :fieldValue')
            ->setParameter('fieldName', Field::DATE_ADDED->value)
            ->setParameter('fieldValue', NewCreator::getCutoffDateStr())
            ->orderBy('d_cv.value', 'DESC')
            // TODO: No pagination. https://github.com/veelkoov/fuzzrake/issues/248
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Generator<Creator>
     */
    private function getPaged(QueryBuilder $queryBuilder): Generator
    {
        $query = $queryBuilder->getQuery();
        $first = 0;
        $total = 1; // Temporary false value to start the loop

        while ($first < $total) {
            $query->setFirstResult($first)->setMaxResults(Pagination::PAGE_SIZE);
            $creatorsPage = new Paginator($query, fetchJoinCollection: true);

            $total = $creatorsPage->count();
            $first += Pagination::PAGE_SIZE;

            foreach ($creatorsPage as $creator) {
                yield $creator; // @phpstan-ignore generator.valueType
            }
        }
    }

    /**
     * @return Generator<Creator>
     */
    public function getAllPaged(): Generator
    {
        return $this->getPaged($this->getCreatorsQueryBuilder());
    }

    /**
     * @return Generator<Creator>
     */
    public function getActivePaged(): Generator
    {
        $queryBuilder = $this->getCreatorsQueryBuilder()
            ->where('d_c.inactiveReason = :empty')
            ->setParameter('empty', '')
            ->orderBy('d_c.id')
        ;

        return $this->getPaged($queryBuilder);
    }

    /**
     * @param list<string> $items
     *
     * @return Generator<Creator>
     */
    public function getWithOtherItemsLikePaged(array $items): Generator
    {
        $items = StringToString::mapFrom($items, static fn (string $value, int $key) => ["item$key", $value]);

        $parameters = new ArrayCollection([
            new Parameter('empty', ''),
            new Parameter('otherFieldNames', [
                Field::OTHER_FEATURES->value,
                Field::OTHER_ORDER_TYPES->value,
                Field::OTHER_STYLES->value,
            ]),
            // grep-code-debris-needs-improvements
            ...$items->map(static fn (string $key, string $value) => [$key, new Parameter($key, "%$value%")]),
        ]);

        $queryBuilder = $this->getCreatorsQueryBuilder();
        $queryBuilder
            ->where('d_c.inactiveReason = :empty')
            ->andWhere($queryBuilder->expr()->exists(
                $this->getEntityManager()->getRepository(CreatorValue::class)->createQueryBuilder('cv')
                    ->select('1')
                    ->where('d_cv.creator = d_c')
                    ->andWhere('d_cv.fieldName IN (:otherFieldNames)')
                    ->andWhere($queryBuilder->expr()->orX(
                        ...$items->getKeys()->map(static fn (string $parName) => "cv.value LIKE :$parName")
                    )),
            ))
            ->setParameters($parameters)
            ->orderBy('d_c.id')
        ;

        return $this->getPaged($queryBuilder);
    }

    private function getCreatorsQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('d_c')
            ->leftJoin('d_c.volatileData', 'd_cvd')->addSelect('d_cvd')
            ->leftJoin('d_c.privateData', 'd_cpd')->addSelect('d_cpd')
            ->leftJoin('d_c.urls', 'd_cu')->addSelect('d_cu')
            ->leftJoin('d_cu.state', 'd_cus')->addSelect('d_cus')
            ->leftJoin('d_c.offerStatuses', 'd_cos')->addSelect('d_cos')
            ->leftJoin('d_c.creatorIds', 'd_ci')->addSelect('d_ci')
            ->leftJoin('d_c.values', 'd_cv')->addSelect('d_cv')
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

    public function getPaymentPlans(): StringList
    {
        $result = $this->createQueryBuilder('d_c')
            ->select('d_c.paymentPlans AS paymentPlans')
            ->where('d_c.inactiveReason = :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleColumnResult();

        return new StringList($result); // @phpstan-ignore argument.type (Lack of skill to fix this)
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
     * @param string[] $creatorIds
     *
     * @return Creator[]
     */
    public function findByCreatorIds(array $creatorIds): array
    {
        $builder = $this->createQueryBuilder('d_c')
            ->leftJoin('d_c.creatorIds', 'd_ci');

        $i = 0;

        foreach ($creatorIds as $creatorId) {
            $builder->orWhere("d_ci.creatorId = :eq$i");
            $builder->setParameter("eq$i", $creatorId);
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

    /**
     * @return Paginator<array{0: Creator}>
     */
    public function getFiltered(QueryChoicesAppender $appender): Paginator
    {
        $builder = $this->getCreatorsQueryBuilder();

        $appender->applyChoices($builder);

        return new Paginator($builder->getQuery(), fetchJoinCollection: true); // @phpstan-ignore return.type (grep-code-cannot-use-coalesce-in-doctrine-order-by)
    }

    public function getWithIds(IntList $idsOfCreators): CreatorList
    {
        $entities = $this->getCreatorsQueryBuilder()
            ->where('d_c.id IN (:idsOfCreators)')
            ->setParameter('idsOfCreators', $idsOfCreators)
            ->getQuery()
            ->getResult();

        return CreatorList::wrap($entities);
    }
}
