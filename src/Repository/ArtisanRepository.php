<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Artisan;
use App\Utils\Artisan\ValidationRegexps;
use App\Utils\FilterItems;
use App\Utils\Regexp\Regexp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;

/**
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

    /**
     * @return Artisan[]
     */
    public function getAll(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.commissionsStatus', 'cs')
            ->leftJoin('a.urls', 'u')
            ->leftJoin('a.privateData', 'pd') // Even if unneded, we have to, because "Inverse side of x-to-one can never be lazy"
            ->addSelect('cs')
            ->addSelect('u')
            ->addSelect('pd')
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->enableResultCache(3600)
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getDistinctCountriesCount(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT (DISTINCT a.country)')
            ->where('a.country != \'\'')
            ->getQuery()
            ->enableResultCache(3600)
            ->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getCommissionsStats(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('open', 'open', 'integer');
        $rsm->addScalarResult('closed', 'closed', 'integer');
        $rsm->addScalarResult('tracked', 'tracked', 'integer');
        $rsm->addScalarResult('successfully_tracked', 'successfully_tracked', 'integer');
        $rsm->addScalarResult('total', 'total', 'integer');

        return $this
            ->getEntityManager()
            ->createNativeQuery('
                SELECT SUM(acs.status = 1) AS open
                    , SUM(acs.status = 0) AS closed
                    , SUM(acs.status IS NOT NULL AND au_cst.url <> \'\') AS successfully_tracked
                    , SUM(au_cst.url <> \'\') AS tracked
                    , SUM(1) AS total
                FROM artisans AS a
                LEFT JOIN artisans_commissions_statues AS acs
                    ON a.id = acs.artisan_id
                LEFT JOIN artisans_urls AS au_cst
                    ON a.id = au_cst.artisan_id AND au_cst.type = \'URL_CST\'
                WHERE a.inactive_reason = \'\'
            ', $rsm)
            ->enableResultCache(3600)
            ->getSingleResult(NativeQuery::HYDRATE_ARRAY);
    }

    public function getDistinctCountriesToCountAssoc(): FilterItems
    {
        return $this->getDistinctItemsWithCountFromJoined('country');
    }

    public function getDistinctStatesToCountAssoc(): FilterItems
    {
        return $this->getDistinctItemsWithCountFromJoined('state');
    }

    public function getDistinctOrderTypes(): FilterItems
    {
        return $this->getDistinctItemsWithCountFromJoined('orderTypes', true);
    }

    public function getDistinctOtherOrderTypes(): FilterItems
    {
        return $this->getDistinctItemsWithCountFromJoined('otherOrderTypes');
    }

    public function getDistinctStyles(): FilterItems
    {
        return $this->getDistinctItemsWithCountFromJoined('styles', true);
    }

    public function getDistinctOtherStyles(): FilterItems
    {
        return $this->getDistinctItemsWithCountFromJoined('otherStyles');
    }

    public function getDistinctFeatures(): FilterItems
    {
        return $this->getDistinctItemsWithCountFromJoined('features', true);
    }

    public function getDistinctOtherFeatures(): FilterItems
    {
        return $this->getDistinctItemsWithCountFromJoined('otherFeatures');
    }

    public function getDistinctProductionModels(): FilterItems
    {
        return $this->getDistinctItemsWithCountFromJoined('productionModels');
    }

    public function getDistinctLanguages(): FilterItems
    {
        return $this->getDistinctItemsWithCountFromJoined('languages');
    }

    public function getDistinctCommissionStatuses(): FilterItems
    {
        $rows = $this->createQueryBuilder('a')
            ->leftJoin('a.commissionsStatus', 's')
            ->select("s.status, COUNT(COALESCE(s.status, 'null')) AS count")
            ->where('a.inactiveReason = :empty')
            ->setParameter('empty', '')
            ->groupBy('s.status')
            ->getQuery()
            ->enableResultCache(3600)
            ->getArrayResult();

        $result = new FilterItems(false);
        $result->addComplexItem('1', '1', 'Open', 0);
        $result->addComplexItem('0', '0', 'Closed', 0);

        foreach ($rows as $row) {
            if (null === $row['status']) {
                $result->incUnknownCount((int) $row['count']);
            } else {
                $result[(int) $row['status']]->incCount((int) $row['count']);
            }
        }

        return $result;
    }

    private function getDistinctItemsWithCountFromJoined(string $columnName, bool $countOther = false): FilterItems
    {
        $rows = $this->fetchColumnsAsArray($columnName, $countOther);

        $result = new FilterItems($countOther);

        foreach ($rows as $row) {
            $items = explode("\n", $row['items']);

            foreach ($items as $item) {
                if (($item = trim($item))) {
                    $result->addOrIncItem($item);
                }
            }

            if ($countOther && !empty($row['otherItems'])) {
                $result->incOtherCount();
            }

            if (empty($row['items']) && (!$countOther || empty($row['otherItems']))) {
                $result->incUnknownCount();
            }
        }

        $result->sort();

        return $result;
    }

    /**
     * @return Artisan[]
     */
    public function findBestMatches(array $names, array $makerIds, ?string $matchedName): array
    {
        $builder = $this->createQueryBuilder('a')->setParameter('empty', '');
        $i = 0;

        if (null !== $matchedName) {
            array_push($names, $matchedName);
        }

        foreach ($names as $name) {
            $builder->orWhere("a.name = :eq$i OR (a.formerly <> :empty AND a.formerly LIKE :like$i)");
            $builder->setParameter("eq$i", $name);
            $builder->setParameter("like$i", "%$name%");
            ++$i;
        }

        foreach ($makerIds as $makerId) {
            $builder->orWhere("a.makerId = :eq$i OR (a.formerMakerIds <> :empty AND a.formerMakerIds LIKE :like$i)");
            $builder->setParameter("eq$i", $makerId);
            $builder->setParameter("like$i", "%$makerId%");
            ++$i;
        }

        return $builder->getQuery()
            ->enableResultCache(3600)
            ->getResult();
    }

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

        return $queryBuilder->getQuery()
            ->enableResultCache(3600)
            ->getArrayResult();
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findByMakerId(string $makerId): Artisan
    {
        if (!Regexp::match(ValidationRegexps::MAKER_ID, $makerId)) {
            throw new NoResultException();
        }

        return $this->createQueryBuilder('a')
            ->where('
                (a.makerId <> :empty AND a.makerId = :makerId)
                OR a.formerMakerIds = :makerId
                OR a.formerMakerIds LIKE :formerMakerIds1
                OR a.formerMakerIds LIKE :formerMakerIds2
            ')
            ->setParameters([
                'empty'           => '',
                'makerId'         => $makerId,
                'formerMakerIds1' => "%$makerId\n%",
                'formerMakerIds2' => "%\n$makerId%",
            ])
            ->getQuery()
            ->enableResultCache(3600)
            ->getSingleResult();
    }

    /**
     * @return string[]
     */
    public function getOldToNewMakerIdsMap(): array
    {
        $rows = $this->createQueryBuilder('a')
            ->select('a.makerId')
            ->addSelect('a.formerMakerIds')
            ->where('a.makerId <> :empty')
            ->andWhere('a.formerMakerIds <> :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->enableResultCache(3600)
            ->getArrayResult();

        $result = [];

        foreach ($rows as $row) {
            foreach (explode("\n", $row['formerMakerIds']) as $formerMakerId) {
                $result[$formerMakerId] = $row['makerId'];
            }
        }

        return $result;
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
            $parameters["par$i"] = "%{$items[$i]}%";
        }

        return $this->createQueryBuilder('a')
            ->where(implode(' OR ', $ORs))
            ->andWhere('a.inactiveReason = :empty')
            ->setParameters($parameters)
            ->getQuery()
            ->getResult();
    }

    public function getActiveCount(): int
    {
        try {
            return (int) $this->createQueryBuilder('a')
                ->select('COUNT(a)')
                ->where('a.inactiveReason = :empty')
                ->setParameter('empty', '')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            throw new RuntimeException($e);
        }
    }
}
