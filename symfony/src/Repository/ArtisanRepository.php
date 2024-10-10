<?php

declare(strict_types=1);

namespace App\Repository;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\NewArtisan;
use App\Entity\Artisan;
use App\Filtering\DataRequests\Pagination;
use App\Filtering\DataRequests\QueryChoicesAppender;
use App\Utils\Arrays\Arrays;
use App\Utils\Artisan\SmartAccessDecorator as ArtisanSAD;
use App\Utils\Creator\CreatorId;
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
            ->setMaxResults(Pagination::PAGE_SIZE) // TODO Guarantees memory limit, but requires an issue.
            ->getQuery()
            ->getResult();
    }

    private function getPaged(QueryBuilder $queryBuilder, bool $flushAfterPage): Generator
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

            if ($flushAfterPage) {
                $this->getEntityManager()->flush();
            }

            $this->getEntityManager()->clear();
        }
    }

    /**
     * @return Generator<Artisan>
     */
    public function getAllPaged(bool $flushAfterPage = false): Generator
    {
        return $this->getPaged($this->getArtisansQueryBuilder(), $flushAfterPage);
    }

    /**
     * @return Generator<Artisan>
     */
    public function getActivePaged(bool $flushAfterPage = false): Generator
    {
        $queryBuilder = $this->getArtisansQueryBuilder()
            ->where('a.inactiveReason = :empty')
            ->setParameter('empty', '')
        ;

        return $this->getPaged($queryBuilder, $flushAfterPage);
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
     * @throws NonUniqueResultException
     * @throws NoResultException
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

    /**
     * @return list<string>
     */
    public function getDistinctCountries(): array
    {
        $result = $this->createQueryBuilder('a')
            ->select('DISTINCT a.country')
            ->getQuery()
            ->getSingleColumnResult();

        return $result; // @phpstan-ignore-line Lack of skill to fix this
    }

    /**
     * @return list<string>
     */
    public function getDistinctStates(): array
    {
        $result = $this->createQueryBuilder('a')
            ->select('DISTINCT a.state')
            ->getQuery()
            ->getSingleColumnResult();

        return $result; // @phpstan-ignore-line Lack of skill to fix this
    }

    /**
     * @return string[]
     */
    public function getPaymentPlans(): array
    {
        $resultData = $this->createQueryBuilder('a')
            ->select('a.paymentPlans AS paymentPlans')
            ->where('a.inactiveReason = :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleColumnResult();

        return $resultData; // @phpstan-ignore-line Lack of skill to fix this
    }

    /**
     * @return array<string, int>
     */
    public function countDistinctInActiveCreators(string $columnName): array
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

        return Arrays::assoc($result, 'value', 'count'); // @phpstan-ignore-line Lack of skill to fix this
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

    /**
     * @param string[] $items
     *
     * @return Artisan[]
     */
    public function getOthersLike(array $items): array // FIXME
    {
        $ORs = [];
        $parameters = new ArrayCollection([
            new Parameter('empty', ''),
        ]);

        foreach ($items as $i => $item) {
            $ORs[] = "a.otherOrderTypes LIKE :par$i OR a.otherStyles LIKE :par$i OR a.otherFeatures LIKE :par$i";
            $parameters->add(new Parameter("par$i", "%$item%"));
        }

        $builder = $this->createQueryBuilder('a')
            ->where('a.inactiveReason = :empty');

        if ([] !== $ORs) {
            $builder->andWhere(implode(' OR ', $ORs));
        }

        $resultData = $builder
            ->setParameters($parameters)
            ->getQuery()
            ->getResult();

        return $resultData;
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
     * @throws NonUniqueResultException
     * @throws NoResultException
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
            ->orderBy('ZERO_LENGTH(a.inactiveReason)') // Put inactive makers at the end of the list
            ->addOrderBy('LOWER(a.name)')
            ->getQuery();

        $appender->applyPaging($query);

        return new Paginator($query, fetchJoinCollection: true);
    }
}
