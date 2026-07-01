<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Event;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Veelkoov\Debris\Vecs\StringVec;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Event[]
     *
     * @throws DateTimeException
     */
    public function getRecent(?StringVec $types = null): array
    {
        $query = $this->createQueryBuilder('d_e')
            ->where('d_e.timestamp >= :oldest')
            ->orderBy('d_e.timestamp', 'DESC')
            ->setParameter('oldest', UtcClock::at('-31 days'));

        if (true === $types?->isNotEmpty()) {
            $query
                ->andWhere('d_e.type IN (:types)')
                ->setParameter('types', $types);
        }

        return $query
            ->getQuery()
            ->getResult();
    }

    public function getLatestEventTimestamp(): ?DateTimeImmutable
    {
        $resultData = $this
            ->createQueryBuilder('d_e')
            ->select('MAX(d_e.timestamp)')
            ->getQuery()
            ->getSingleScalarResult();

        return null === $resultData ? null : UtcClock::at((string) $resultData);
    }
}
