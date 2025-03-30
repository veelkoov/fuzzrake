<?php

declare(strict_types=1);

namespace App\Repository;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\NewArtisan;
use App\Entity\Artisan;
use App\Entity\ArtisanValue;
use App\Filtering\DataRequests\QueryChoicesAppender;
use App\Utils\Artisan\SmartAccessDecorator as ArtisanSAD;
use App\Utils\Collections\StringList;
use App\Utils\Creator\CreatorId;
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
use Psl\Dict;
use Psl\Vec;
use Veelkoov\Debris\StringIntMap;
use Veelkoov\Debris\StringSet;

/**
 * @extends ServiceEntityRepository<Artisan>
 *
 * @method Artisan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Artisan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Artisan[]    findAll()
 * @method Artisan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtisanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artisan::class);
    }

    public function add(Artisan|ArtisanSAD $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Artisan|ArtisanSAD $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Artisan[]
     */
    public function getNewWithLimit(): array
    {
        return $this->getArtisansQueryBuilder()
            ->where('v.fieldName = :fieldName')
            ->andWhere('v.value > :fieldValue')
            ->setParameter('fieldName', Field::DATE_ADDED->value)
            ->setParameter('fieldValue', NewArtisan::getCutoffDateStr())
            ->orderBy('v.value', 'DESC')
            // TODO: No pagination. https://github.com/veelkoov/fuzzrake/issues/248
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

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
                yield $creator;
            }
        }
    }

    /**
     * @return Generator<Artisan>
     */
    public function getAllPaged(): Generator
    {
        return $this->getPaged($this->getArtisansQueryBuilder());
    }

    /**
     * @return Generator<Artisan>
     */
    public function getActivePaged(): Generator
    {
        $queryBuilder = $this->getArtisansQueryBuilder()
            ->where('a.inactiveReason = :empty')
            ->setParameter('empty', '')
        ;

        return $this->getPaged($queryBuilder);
    }

    /**
     * @param list<string> $items
     *
     * @return Generator<Artisan>
     */
    public function getWithOtherItemsLikePaged(array $items): Generator
    {
        $items = Dict\map_keys($items, fn (int $key): string => "item$key");

        $parameters = new ArrayCollection([
            new Parameter('empty', ''),
            new Parameter('otherFieldNames', [
                Field::OTHER_FEATURES->value,
                Field::OTHER_ORDER_TYPES->value,
                Field::OTHER_STYLES->value,
            ]),
            ...Vec\map_with_key($items, fn (string $key, string $value) => new Parameter($key, "%$value%")),
        ]);

        $queryBuilder = $this->getArtisansQueryBuilder();
        $queryBuilder
            ->where('a.inactiveReason = :empty')
            ->andWhere($queryBuilder->expr()->exists(
                $this->getEntityManager()->getRepository(ArtisanValue::class)->createQueryBuilder('cv')
                    ->select('1')
                    ->where('cv.artisan = a')
                    ->andWhere('cv.fieldName IN (:otherFieldNames)')
                    ->andWhere($queryBuilder->expr()->orX(
                        ...Vec\map_with_key($items, fn (string $parName, string $_): string => "cv.value LIKE :$parName"),
                    )),
            ))
            ->setParameters($parameters)
        ;

        return $this->getPaged($queryBuilder);
    }

    private function getArtisansQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.volatileData', 'vd')->addSelect('vd')
            ->leftJoin('a.privateData', 'apd')->addSelect('apd')
            ->leftJoin('a.urls', 'u')->addSelect('u')
            ->leftJoin('u.state', 'us')->addSelect('us')
            ->leftJoin('a.commissions', 'c')->addSelect('c')
            ->leftJoin('a.makerIds', 'mi')->addSelect('mi')
            ->leftJoin('a.values', 'v')->addSelect('v')
            ->orderBy('a.name', 'ASC');
    }

    /**
     * @throws UnexpectedResultException
     */
    public function getDistinctCountriesCount(): int
    {
        $resultData = $this->createQueryBuilder('a')
            ->select('COUNT (DISTINCT a.country)')
            ->where('a.country != \'\'')
            ->andWhere('a.country != \'EU\'') // grep-country-eu
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $resultData;
    }

    public function getDistinctCountries(): StringSet
    {
        $result = $this->createQueryBuilder('a')
            ->select('DISTINCT a.country')
            ->getQuery()
            ->getSingleColumnResult();

        return new StringSet($result); // @phpstan-ignore argument.type (Lack of skill to fix this)
    }

    public function getDistinctStates(): StringSet
    {
        $result = $this->createQueryBuilder('a')
            ->select('DISTINCT a.state')
            ->getQuery()
            ->getSingleColumnResult();

        return new StringSet($result); // @phpstan-ignore argument.type (Lack of skill to fix this)
    }

    public function getPaymentPlans(): StringList
    {
        $result = $this->createQueryBuilder('a')
            ->select('a.paymentPlans AS paymentPlans')
            ->where('a.inactiveReason = :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleColumnResult();

        return new StringList($result); // @phpstan-ignore argument.type (Lack of skill to fix this)
    }

    /**
     * @param literal-string $columnName
     */
    public function countDistinctInActiveCreators(string $columnName): StringIntMap
    {
        $result = $this->getEntityManager()->createQuery("
            SELECT c.$columnName AS value, COUNT(c.$columnName) AS count
            FROM \\App\\Entity\\Artisan AS c
            WHERE c.inactiveReason = :empty
            GROUP BY c.$columnName
            ORDER BY count
        ")
            ->setParameter('empty', '')
            ->getArrayResult();

        return StringIntMap::fromRows($result, 'value', 'count');
    }

    /**
     * @param string[] $names
     * @param string[] $makerIds
     *
     * @return Artisan[]
     */
    public function findBestMatches(array $names, array $makerIds): array
    {
        $builder = $this->createQueryBuilder('a')
            ->leftJoin('a.makerIds', 'm');

        $i = 0;

        foreach ($names as $name) {
            $builder->orWhere("a.name = :eq$i OR (a.formerly <> '' AND a.formerly LIKE :like$i)");
            $builder->setParameter("eq$i", $name);
            $builder->setParameter("like$i", "%$name%");
            ++$i;
        }

        foreach ($makerIds as $makerId) {
            $builder->orWhere("m.makerId = :eq$i");
            $builder->setParameter("eq$i", $makerId);
            ++$i;
        }

        $resultData = $builder->getQuery()->getResult();

        return $resultData;
    }

    /**
     * @throws NoResultException
     */
    public function findByMakerId(string $makerId): Artisan
    {
        if (!CreatorId::isValid($makerId)) {
            throw new NoResultException();
        }

        try {
            $resultData = $this->createQueryBuilder('a')
                ->join('a.makerIds', 'm_where')
                ->where('m_where.makerId = :makerId')
                ->setParameter('makerId', $makerId)
                ->getQuery()
                ->getSingleResult();
        } catch (NonUniqueResultException $e) { // @codeCoverageIgnoreStart
            throw new UnbelievableRuntimeException($e);
        } // @codeCoverageIgnoreEnd

        return $resultData; // @phpstan-ignore-line Lack of skill to fix this
    }

    public function countActive(): int
    {
        $resultData = $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->where('a.inactiveReason = :empty')
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
        $resultData = $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $resultData;
    }

    /**
     * @throws UnexpectedResultException
     */
    public function getCsTrackedCount(): int
    {
        $resultData = $this->createQueryBuilder('a')
            ->leftJoin('a.urls', 'au')
            ->select('COUNT(DISTINCT a.id)')
            ->where('au.type = :type')
            ->andWhere('a.inactiveReason = :empty')
            ->setParameter('type', Field::URL_COMMISSIONS->value)
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $resultData;
    }

    /**
     * @return Paginator<Artisan>
     */
    public function getFiltered(QueryChoicesAppender $appender): Paginator
    {
        $builder = $this->getArtisansQueryBuilder();

        $appender->applyChoices($builder);

        $query = $builder
            ->orderBy('LOWER(a.name)')
            ->getQuery();

        $appender->applyPaging($query);

        return new Paginator($query, fetchJoinCollection: true);
    }
}
