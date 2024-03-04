<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArtisanValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
}
