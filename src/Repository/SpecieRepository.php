<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Specie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Specie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Specie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Specie[]    findAll()
 * @method Specie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Specie>
 */
class SpecieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Specie::class);
    }
}
