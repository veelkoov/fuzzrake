<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MakerId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MakerId|null find($id, $lockMode = null, $lockVersion = null)
 * @method MakerId|null findOneBy(array $criteria, array $orderBy = null)
 * @method MakerId[]    findAll()
 * @method MakerId[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<MakerId>
 */
class MakerIdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MakerId::class);
    }
}
