<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CreatorUrlState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CreatorUrlState>
 */
class CreatorUrlStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreatorUrlState::class);
    }
}
