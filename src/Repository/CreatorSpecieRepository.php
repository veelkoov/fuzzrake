<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CreatorSpecie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Veelkoov\Debris\Maps\StringToInt;

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

    public function getActiveCreatorsSpecieNamesToCount(): StringToInt
    {
        $result = $this->getEntityManager()->createQuery('
                SELECT d_s.name AS name
                    , COUNT(d_cs) AS count
                FROM App\Entity\CreatorSpecie AS d_cs
                JOIN d_cs.creator AS d_c
                JOIN d_cs.specie AS d_s
                WHERE d_c.inactiveReason = :empty
                GROUP BY name
            ')
            ->setParameter('empty', '')
            ->getArrayResult();

        return StringToInt::fromRows($result, 'name', 'count');
    }

    public function countActiveCreatorsHavingSpeciesDefined(): int
    {
        $result = $this->getEntityManager()->createQuery('
                SELECT COUNT (DISTINCT d_c)
                FROM App\Entity\CreatorSpecie d_cs
                JOIN d_cs.creator AS d_c
                WHERE d_c.inactiveReason = :empty
            ')
            ->setParameter('empty', '')
            ->getSingleScalarResult();

        return (int) $result;
    }
}
