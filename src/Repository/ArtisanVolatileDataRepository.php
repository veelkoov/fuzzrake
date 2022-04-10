<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArtisanVolatileData;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArtisanVolatileData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtisanVolatileData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtisanVolatileData[]    findAll()
 * @method ArtisanVolatileData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtisanVolatileDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtisanVolatileData::class);
    }

    /**
     * @throws DateTimeException
     * @throws UnexpectedResultException
     */
    public function getLastCsUpdateTime(): DateTimeImmutable
    {
        return UtcClock::at($this
            ->createQueryBuilder('avd')
            ->select('MAX(avd.lastCsUpdate)')
            ->getQuery()
            ->enableResultCache(3600)
            ->getSingleScalarResult());
    }

    /**
     * @throws DateTimeException
     * @throws UnexpectedResultException
     */
    public function getLastBpUpdateTime(): DateTimeImmutable
    {
        return UtcClock::at($this
            ->createQueryBuilder('avd')
            ->select('MAX(avd.lastBpUpdate)')
            ->getQuery()
            ->enableResultCache(3600)
            ->getSingleScalarResult());
    }

    /**
     * @throws UnexpectedResultException
     */
    public function getCsTrackingIssuesCount(): int
    {
        return (int) $this->createQueryBuilder('avd')
            ->select('COUNT(avd.id)')
            ->where('avd.csTrackerIssue = :true')
            ->setParameter('true', true)
            ->getQuery()
            ->enableResultCache(3600)
            ->getSingleScalarResult();
    }
}
