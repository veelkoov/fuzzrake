<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CreatorSpecie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Veelkoov\Debris\StringIntMap;

/**
 * @method CreatorSpecie|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreatorSpecie|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreatorSpecie[]    findAll()
 * @method CreatorSpecie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<CreatorSpecie>
 */
class CreatorSpecieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreatorSpecie::class);
    }

    public function getActiveCreatorsSpecieNamesToCount(): StringIntMap
    {
        $result = $this->getEntityManager()->createQuery('
                SELECT s.name AS name
                    , COUNT(cs) AS count
                FROM App\Entity\CreatorSpecie AS cs
                JOIN cs.creator AS c
                JOIN cs.specie AS s
                WHERE c.inactiveReason = :empty
                GROUP BY name
            ')
            ->setParameter('empty', '')
            ->getArrayResult();

        return StringIntMap::fromRows($result, 'name', 'count');
    }

    public function countActiveCreatorsHavingSpeciesDefined(): int
    {
        $result = $this->getEntityManager()->createQuery('
                SELECT COUNT (DISTINCT c)
                FROM App\Entity\CreatorSpecie cs
                JOIN cs.creator AS c
                WHERE c.inactiveReason = :empty
            ')
            ->setParameter('empty', '')
            ->getSingleScalarResult();

        return (int) $result;
    }
}
