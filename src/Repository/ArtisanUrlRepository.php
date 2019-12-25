<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArtisanUrl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ArtisanUrl|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtisanUrl|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtisanUrl[]    findAll()
 * @method ArtisanUrl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtisanUrlRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ArtisanUrl::class);
    }
}
