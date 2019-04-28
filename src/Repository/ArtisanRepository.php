<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Artisan;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Artisan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Artisan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Artisan[]    findAll()
 * @method Artisan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtisanRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Artisan::class);
    }

    public function getAll(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getDistinctCountriesCount(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT (DISTINCT a.country)')
            ->where('a.country != \'\'')
            ->getQuery()
            ->getSingleScalarResult();
    }

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
                SELECT SUM(are_commissions_open = 1) AS open
                  , SUM(are_commissions_open = 0) AS closed
                  , SUM(are_commissions_open IS NOT NULL AND commissions_quotes_check_url <> "") AS successfully_tracked
                  , SUM(commissions_quotes_check_url <> "") AS tracked
                  , SUM(1) AS total
                FROM artisans
            ', $rsm)
            ->getSingleResult(NativeQuery::HYDRATE_ARRAY);
    }

    public function getDistinctCountriesToCountAssoc(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('country');
    }

    public function getDistinctOrderTypes(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('orderTypes', true);
    }

    public function getDistinctOtherOrderTypes(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('otherOrderTypes');
    }

    public function getDistinctStyles(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('styles', true);
    }

    public function getDistinctOtherStyles(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('otherStyles');
    }

    public function getDistinctFeatures(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('features', true);
    }

    public function getDistinctOtherFeatures(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('otherFeatures');
    }

    public function getDistinctProductionModels(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('productionModels');
    }

    private function getDistinctItemsWithCountFromJoined(string $columnName, bool $countOther = false): array
    {
        $rows = $this->fetchColumnsAsArray($columnName, $countOther);

        $result = [
            'items' => [],
            'unknown_count' => 0,
        ];

        if ($countOther) {
            $result['other_count'] = 0;
        }

        foreach ($rows as $row) {
            $items = explode("\n", $row['items']);

            foreach ($items as $item) {
                if ($item = trim($item)) {
                    if (!array_key_exists($item, $result['items'])) {
                        $result['items'][$item] = 0;
                    }

                    ++$result['items'][$item];
                }
            }

            if ($countOther && !empty($row['otherItems'])) {
                ++$result['other_count'];
            }

            if (empty($row['items']) && (!$countOther || empty($row['otherItems']))) {
                ++$result['unknown_count'];
            }
        }

        ksort($result['items']);

        return $result;
    }

    public function getLastCstUpdateTime(): DateTime
    {
        return new DateTime($this
            ->createQueryBuilder('a')
            ->select('MAX(a.commissionsQuotesLastCheck)')
            ->getQuery()
            ->getSingleScalarResult(), new DateTimeZone('UTC'));
    }

    public function findBestMatches(array $names, array $makerIds, ?string $matchedName): array
    {
        $builder = $this->createQueryBuilder('a')->setParameter('empty', '');
        $i = 0;

        if ($matchedName !== null) {
            array_push($names, $matchedName);
        }

        foreach ($names as $name) {
            $builder->orWhere("a.name = :eq$i OR (a.formerly <> :empty AND a.formerly LIKE :like$i)");
            $builder->setParameter("eq$i", $name);
            $builder->setParameter("like$i", "%$name%");
            $i++;
        }

        foreach ($makerIds as $makerId) {
            $builder->orWhere("a.makerId = :eq$i OR (a.formerMakerIds <> :empty AND a.formerMakerIds LIKE :like$i)");
            $builder->setParameter("eq$i", $makerId);
            $builder->setParameter("like$i", "%$makerId%");
            $i++;
        }

        return $builder->getQuery()->getResult();
    }

    public function getOtherItemsData()
    {
        $ot = $this->getDistinctOtherOrderTypes();
        $fe = $this->getDistinctOtherFeatures();
        $st = $this->getDistinctOtherStyles();

        foreach (['OT' => &$ot, 'FE' => &$fe, 'ST' => &$st] as $suffix => &$items) {
            $items = array_combine(
                array_map(function ($key) use ($suffix) {
                    return "$key ($suffix)";
                }, array_keys($items['items'])),
                array_values($items['items'])
            );
        }

        $result = array_merge($ot, $fe, $st);
        ksort($result);

        return $result;
    }

    /**
     * @param string $columnName
     * @param bool   $includeOther
     *
     * @return array
     */
    private function fetchColumnsAsArray(string $columnName, bool $includeOther): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select("a.$columnName AS items");

        if ($includeOther) {
            $otherColumnName = 'other'.ucfirst($columnName);
            $queryBuilder->addSelect("a.$otherColumnName AS otherItems");
        }

        return $queryBuilder->getQuery()->getArrayResult();
    }
}
