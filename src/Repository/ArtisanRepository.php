<?php

declare(strict_types=1);

namespace App\Repository;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\ValidationRegexps;
use App\DataDefinitions\NewArtisan;
use App\Entity\Artisan;
use App\Utils\Filters\FilterData;
use App\Utils\Filters\SpecialItems;
use App\Utils\UnbelievableRuntimeException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
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

    public function add(Artisan $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

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
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->enableResultCache(3600)
            ->getResult();

        return $resultData; // @phpstan-ignore-line Lack of skill to fix this
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
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->enableResultCache(3600)
            ->getResult();

        return $resultData; // @phpstan-ignore-line Lack of skill to fix this
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
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->enableResultCache(3600)
            ->getResult();

        return $resultData; // @phpstan-ignore-line Lack of skill to fix this
    }

    private function getArtisansQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.volatileData', 'vd')->addSelect('vd')
            ->leftJoin('a.urls', 'u')->addSelect('u')
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

        return (int) $resultData; // @phpstan-ignore-line Lack of skill to fix this
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

    public function getDistinctLanguages(): FilterData
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

        $result = new FilterData(...$special);

        foreach ($rows as $row) {
            $items = explode("\n", $row['items']);

            foreach ($items as $item) {
                if ($item = trim($item)) {
                    $result->getItems()->addOrIncItem($item);
                }
            }

            if ($countOther && !empty($row['otherItems'])) {
                $other->incCount(); // @phpstan-ignore-line if $countOther guarantees the variable being defined
            }

            if (empty($row['items']) && (!$countOther || empty($row['otherItems']))) {
                $unknown->incCount();
            }
        }

        $result->getItems()->sort();

        return $result;
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

        return $resultData; // @phpstan-ignore-line Lack of skill to fix this
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
        if (pattern(ValidationRegexps::MAKER_ID)->fails($makerId)) {
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
        $parameters = [
            'empty' => '',
        ];

        foreach ($items as $i => $item) {
            $ORs[] = "a.otherOrderTypes LIKE :par$i OR a.otherStyles LIKE :par$i OR a.otherFeatures LIKE :par$i";
            $parameters["par$i"] = "%$item%";
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

        return $resultData; // @phpstan-ignore-line Lack of skill to fix this
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

        return (int) $resultData; // @phpstan-ignore-line Lack of skill to fix this
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
            ->setParameters([
                'type'  => Field::URL_COMMISSIONS->name,
                'empty' => '',
            ])
            ->getQuery()
            ->enableResultCache(3600)
            ->getSingleScalarResult();

        return (int) $resultData; // @phpstan-ignore-line Lack of skill to fix this
    }
}
