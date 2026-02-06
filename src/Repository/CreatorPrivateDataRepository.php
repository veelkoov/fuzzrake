<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CreatorPrivateData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CreatorPrivateData|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreatorPrivateData|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreatorPrivateData[]    findAll()
 * @method CreatorPrivateData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<CreatorPrivateData>
 */
class CreatorPrivateDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreatorPrivateData::class);
    }
}
