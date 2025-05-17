<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CreatorVolatileData;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CreatorVolatileData|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreatorVolatileData|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreatorVolatileData[]    findAll()
 * @method CreatorVolatileData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<CreatorVolatileData>
 */
class CreatorVolatileDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreatorVolatileData::class);
    }

    /**
     * @throws DateTimeException
     * @throws UnexpectedResultException
     */
    public function getLastCsUpdateTime(): ?DateTimeImmutable
    {
        $resultData = $this
            ->createQueryBuilder('d_cvd')
            ->select('MAX(d_cvd.lastCsUpdate)')
            ->getQuery()
            ->getSingleScalarResult();

        return null === $resultData ? null : UtcClock::at((string) $resultData);
    }

    /**
     * @throws UnexpectedResultException
     */
    public function getCsTrackingIssuesCount(): int
    {
        $resultData = $this->createQueryBuilder('d_cvd')
            ->select('COUNT(d_cvd.id)')
            ->where('d_cvd.csTrackerIssue = :true')
            ->setParameter('true', true)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $resultData;
    }
}
