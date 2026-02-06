<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CreatorId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CreatorId|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreatorId|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreatorId[]    findAll()
 * @method CreatorId[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<CreatorId>
 */
class CreatorIdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreatorId::class);
    }
}
