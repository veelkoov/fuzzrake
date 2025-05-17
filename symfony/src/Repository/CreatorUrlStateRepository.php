<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CreatorUrlState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CreatorUrlState|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreatorUrlState|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreatorUrlState[]    findAll()
 * @method CreatorUrlState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<CreatorUrlState>
 */
class CreatorUrlStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreatorUrlState::class);
    }
}
