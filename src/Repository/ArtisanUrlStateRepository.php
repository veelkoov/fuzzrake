<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArtisanUrlState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArtisanUrlState|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtisanUrlState|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtisanUrlState[]    findAll()
 * @method ArtisanUrlState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtisanUrlStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtisanUrlState::class);
    }
}
