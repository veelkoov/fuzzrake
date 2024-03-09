<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CreatorSpecie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
}
