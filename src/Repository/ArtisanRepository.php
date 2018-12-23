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
                  , SUM(are_commissions_open IS NOT NULL AND commisions_quotes_check_url <> "") AS successfully_tracked
                  , SUM(commisions_quotes_check_url <> "") AS tracked
                  , SUM(1) AS total
                FROM artisans
            ', $rsm)
            ->getSingleResult(NativeQuery::HYDRATE_ARRAY);
    }

    public function getDistinctCountries(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('country');
    }

    public function getDistinctTypes(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('types');
    }

    public function getDistinctOtherTypes(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('otherTypes');
    }

    public function getDistinctStyles(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('styles');
    }

    public function getDistinctOtherStyles(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('otherStyles');
    }

    public function getDistinctFeatures(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('features');
    }

    public function getDistinctOtherFeatures(): array
    {
        return $this->getDistinctItemsWithCountFromJoined('otherFeatures');
    }

    private function getDistinctItemsWithCountFromJoined(string $columnName, string $separator = "\n"): array
    {
        $dbResult = $this->createQueryBuilder('a')
            ->select("a.$columnName AS items")
            ->where("a.$columnName != :empty")
            ->setParameter('empty', '')
            ->getQuery()
            ->getArrayResult();

        $allJoined = implode($separator, array_map(function ($item) {
            return $item['items'];
        }, $dbResult));

        $result = [];

        foreach (explode($separator, $allJoined) as $item) {
            $item = trim($item);

            if (!array_key_exists($item, $result)) {
                $result[$item] = 0;
            }

            ++$result[$item];
        }

        ksort($result);

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

    public function findBestMatches(string $name, string $formerly)
    {
        return $this->createQueryBuilder('a')
            ->setParameters([
                'name' => $name,
                'formerly' => $formerly,
                'empty' => '',
            ])
            ->where('
                a.name = :name
                OR a.name = :formerly
                OR (a.formerly = :formerly AND a.formerly <> :empty)
            ')
            ->getQuery()
            ->getResult();
    }
}
