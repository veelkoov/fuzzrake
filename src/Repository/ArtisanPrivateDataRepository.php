<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArtisanPrivateData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ArtisanPrivateData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtisanPrivateData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtisanPrivateData[]    findAll()
 * @method ArtisanPrivateData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtisanPrivateDataRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ArtisanPrivateData::class);
    }
}
