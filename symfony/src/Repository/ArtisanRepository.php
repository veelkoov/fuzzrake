<?php

declare(strict_types=1);

namespace App\Repository;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\NewArtisan;
use App\Entity\Artisan;
use App\Filtering\DataRequests\QueryChoicesAppender;
use App\Filtering\FiltersData\Builder\MutableFilterData;
use App\Filtering\FiltersData\Builder\SpecialItems;
use App\Filtering\FiltersData\FilterData;
use App\Utils\Artisan\SmartAccessDecorator as ArtisanSAD;
use App\Utils\Creator\CreatorId;
use App\Utils\UnbelievableRuntimeException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;

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
    public function getAll(): array
    {
        $resultData = $this->getArtisansQueryBuilder()
            ->getQuery()
            ->enableResultCache(3600)
            ->getResult();

        return $resultData;
    }

    /**
     * @return Artisan[]
     */
    public function getNew(): array
    {
        $resultData = $this->getArtisansQueryBuilder()
            ->where('v.fieldName = :fieldName')
            ->andWhere('v.value > :fieldValue')
            ->setParameter('fieldName', Field::DATE_ADDED->value)
            ->setParameter('fieldValue', NewArtisan::getCutoffDateStr())
            ->orderBy('v.value', Criteria::DESC)
            ->getQuery()
            ->enableResultCache(3600)
            ->getResult();

        return $resultData;
    }

    /**
     * @return Artisan[]
     */
    public function getActive(): array
    {
        $resultData = $this->getArtisansQueryBuilder()
            ->where('a.inactiveReason = :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->enableResultCache(3600)
            ->getResult();

        return $resultData;
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
            ->orderBy('a.name', Criteria::ASC);
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
            ->enableResultCache(3600)
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
     * @return list<string>
     */
    public function getDistinctLanguages(): array
    {
        $result = $this->createQueryBuilder('a')
            ->select('DISTINCT a.languages')
            ->getQuery()
            ->getSingleColumnResult();

        return $result; // @phpstan-ignore-line Lack of skill to fix this
    }

    public function getDistinctCountriesToCountAssoc(): FilterData
    {
        return $this->getDistinctItemsWithCountFromJoined('country');
    }

    public function getDistinctStatesToCountAssoc(): FilterData
    {
        return $this->getDistinctItemsWithCountFromJoined('state');
    }

    public function getDistinctOrderTypes(): FilterData
    {
        return $this->getDistinctItemsWithCountFromJoined('orderTypes', true);
    }

    public function getDistinctOtherOrderTypes(): FilterData
    {
        return $this->getDistinctItemsWithCountFromJoined('otherOrderTypes');
    }

    public function getDistinctStyles(): FilterData
    {
        return $this->getDistinctItemsWithCountFromJoined('styles', true);
    }

    public function getDistinctOtherStyles(): FilterData
    {
        return $this->getDistinctItemsWithCountFromJoined('otherStyles');
    }

    public function getDistinctFeatures(): FilterData
    {
        return $this->getDistinctItemsWithCountFromJoined('features', true);
    }

    public function getDistinctOtherFeatures(): FilterData
    {
        return $this->getDistinctItemsWithCountFromJoined('otherFeatures');
    }

    public function getDistinctProductionModels(): FilterData
    {
        return $this->getDistinctItemsWithCountFromJoined('productionModels');
    }

    public function getDistinctLanguagesForFilters(): FilterData
    {
        return $this->getDistinctItemsWithCountFromJoined('languages');
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
            ->enableResultCache(3600)
            ->getSingleColumnResult();

        return $resultData; // @phpstan-ignore-line Lack of skill to fix this
    }

    private function getDistinctItemsWithCountFromJoined(string $columnName, bool $countOther = false): FilterData
    {
        $rows = $this->fetchColumnsAsArray($columnName, $countOther);

        $unknown = SpecialItems::newUnknown();
        $special = [$unknown];

        if ($countOther) {
            $other = SpecialItems::newOther();
            $special[] = $other;
        }

        $result = new MutableFilterData(...$special);

        foreach ($rows as $row) {
            $items = explode("\n", $row['items']);

            foreach ($items as $item) {
                if ($item = trim($item)) {
                    $result->items->addOrIncItem($item);
                }
            }

            if ($countOther && !empty($row['otherItems'])) {
                $other->incCount(); // @phpstan-ignore-line if $countOther guarantees the variable being defined
            }

            if (empty($row['items']) && (!$countOther || empty($row['otherItems']))) {
                $unknown->incCount();
            }
        }

        $result->items->sort();

        return FilterData::from($result);
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
     * @return array<array{items: string, otherItems?: string}>
     */
    private function fetchColumnsAsArray(string $columnName, bool $includeOther): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select("a.$columnName AS items")
            ->where('a.inactiveReason = :empty')
            ->setParameter('empty', '');

        if ($includeOther) {
            $otherColumnName = 'other'.ucfirst($columnName);
            $queryBuilder->addSelect("a.$otherColumnName AS otherItems");
        }

        $resultData = $queryBuilder->getQuery()
            ->enableResultCache(3600)
            ->getArrayResult();

        return $resultData; // @phpstan-ignore-line Lack of skill to fix this
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
                ->enableResultCache(3600)
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
    public function getOthersLike(array $items): array
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
            ->enableResultCache(3600)
            ->getResult();

        return $resultData;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countActive(): int
    {
        $resultData = $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->where('a.inactiveReason = :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->enableResultCache(3600)
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
            ->enableResultCache(3600)
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
            ->enableResultCache(3600)
            ->getSingleScalarResult();

        return (int) $resultData;
    }

    /**
     * @return Artisan[]
     */
    public function getFiltered(QueryChoicesAppender $appender): array
    {
        $builder = $this->getArtisansQueryBuilder();

        $appender->applyChoices($builder);

        $result = $builder
            ->orderBy('ZERO_LENGTH(a.inactiveReason)') // Put inactive makers at the end of the list
            ->addOrderBy('LOWER(a.name)')
            ->getQuery()
            ->getResult();

        return $result;
    }
}
