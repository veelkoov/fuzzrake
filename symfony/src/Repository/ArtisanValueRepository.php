<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArtisanValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Veelkoov\Debris\StringIntMap;
use Veelkoov\Debris\StringList;

/**
 * @method ArtisanValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtisanValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtisanValue[]    findAll()
 * @method ArtisanValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<ArtisanValue>
 */
class ArtisanValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtisanValue::class);
    }

    public function getDistinctValues(string $fieldName): StringList
    {
        $result = $this->getEntityManager()->createQuery('
            SELECT DISTINCT cv.value
            FROM \App\Entity\ArtisanValue AS cv
            WHERE cv.fieldName = :fieldName
        ')
            ->setParameter('fieldName', $fieldName)
            ->getSingleColumnResult();

        return new StringList($result); // @phpstan-ignore argument.type (Lack of skill to fix this)
    }

    /**
     * @param list<string> $fieldNames
     */
    public function countActiveCreatorsHavingAnyOf(array $fieldNames): int
    {
        $result = $this->getEntityManager()->createQuery('
            SELECT COUNT(DISTINCT c)
            FROM \App\Entity\ArtisanValue AS cv
            JOIN cv.artisan AS c
            WHERE c.inactiveReason = :empty
            AND cv.fieldName IN (:fieldNames)
        ')
            ->setParameter('fieldNames', $fieldNames)
            ->setParameter('empty', '')
            ->getSingleScalarResult();

        return $result; // @phpstan-ignore-line Lack of skill to fix this
    }

    public function countDistinctInActiveCreatorsHaving(string $fieldName): StringIntMap
    {
        $result = $this->getEntityManager()->createQuery('
            SELECT cv.value AS value, COUNT(cv.value) AS count
            FROM \App\Entity\ArtisanValue AS cv
            JOIN cv.artisan AS c
            WHERE c.inactiveReason = :empty
            AND cv.fieldName = :fieldName
            GROUP BY cv.value
        ')
            ->setParameter('fieldName', $fieldName)
            ->setParameter('empty', '')
            ->getArrayResult();

        return StringIntMap::fromRows($result, 'value', 'count');
    }
}
