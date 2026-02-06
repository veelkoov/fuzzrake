<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CreatorValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Veelkoov\Debris\Maps\StringToInt;
use Veelkoov\Debris\Sets\StringSet;

/**
 * @method CreatorValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreatorValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreatorValue[]    findAll()
 * @method CreatorValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<CreatorValue>
 */
class CreatorValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreatorValue::class);
    }

    public function getDistinctValues(string $fieldName): StringSet
    {
        $result = $this->getEntityManager()->createQuery('
            SELECT DISTINCT d_cv.value
            FROM \App\Entity\CreatorValue AS d_cv
            WHERE d_cv.fieldName = :fieldName
        ')
            ->setParameter('fieldName', $fieldName)
            ->getSingleColumnResult();

        return new StringSet($result); // @phpstan-ignore argument.type (Lack of skill to fix this)
    }

    /**
     * @param list<string> $fieldNames
     */
    public function countActiveCreatorsHavingAnyOf(array $fieldNames): int
    {
        $result = $this->getEntityManager()->createQuery('
            SELECT COUNT(DISTINCT d_c)
            FROM \App\Entity\CreatorValue AS d_cv
            JOIN d_cv.creator AS d_c
            WHERE d_c.inactiveReason = :empty
            AND d_cv.fieldName IN (:fieldNames)
        ')
            ->setParameter('fieldNames', $fieldNames)
            ->setParameter('empty', '')
            ->getSingleScalarResult();

        return $result; // @phpstan-ignore return.type (Lack of skill to fix this)
    }

    public function countDistinctInActiveCreatorsHaving(string $fieldName): StringToInt
    {
        $result = $this->getEntityManager()->createQuery('
            SELECT d_cv.value AS value, COUNT(d_cv.value) AS count
            FROM \App\Entity\CreatorValue AS d_cv
            JOIN d_cv.creator AS d_c
            WHERE d_c.inactiveReason = :empty
            AND d_cv.fieldName = :fieldName
            GROUP BY d_cv.value
        ')
            ->setParameter('fieldName', $fieldName)
            ->setParameter('empty', '')
            ->getArrayResult();

        return StringToInt::fromRows($result, 'value', 'count');
    }
}
