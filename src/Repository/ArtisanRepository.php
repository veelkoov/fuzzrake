<?php

namespace App\Repository;

use App\Entity\Artisan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
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
        return $this->createQueryBuilder('a')
            ->select('COUNT (DISTINCT a.country)')
            ->where('a.country != \'\'')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getDistinctCountries(): array
    {
        return $this->getDistinctItemFromJoined('country');
    }

    public function getDistinctTypes(): array
    {
        return $this->getDistinctItemFromJoined('types');
    }

    public function getDistinctFeatures(): array
    {
        return $this->getDistinctItemFromJoined('features');
    }

    private function getDistinctItemFromJoined(string $columnName): array
    {
        $dbResult = $this->createQueryBuilder('a')
            ->select("DISTINCT a.$columnName AS items")
            ->where("a.$columnName != :empty")
            ->setParameter('empty', '')
            ->getQuery()
            ->getArrayResult();

        $result = [];

        foreach ($dbResult as $items) {
            foreach (explode(',', $items['items']) as $item) {
                $result[trim($item)] = true;
            }
        }

        ksort($result);

        return array_keys($result);
    }
}
