<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArtisanCommissionsStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ArtisanCommissionsStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtisanCommissionsStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtisanCommissionsStatus[]    findAll()
 * @method ArtisanCommissionsStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtisanCommissionsStatusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ArtisanCommissionsStatus::class);
    }
}
