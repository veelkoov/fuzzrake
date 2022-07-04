<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TrackerSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TrackerSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrackerSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrackerSetting[]    findAll()
 * @method TrackerSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<TrackerSetting>
 */
class TrackerSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrackerSetting::class);
    }

    public function removeAll(): void
    {
        $this->createQueryBuilder('ts')->delete()->getQuery()->execute();
    }
}
