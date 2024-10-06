<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Event;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @param string[] $types
     *
     * @return Event[]
     *
     * @throws DateTimeException
     */
    public function getRecent(array $types = []): array
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.timestamp >= :oldest')
            ->orderBy('e.timestamp', 'DESC')
            ->setParameter('oldest', UtcClock::at('-31 days'));

        if ([] !== $types) {
            $query
                ->andWhere('e.type IN (:types)')
                ->setParameter('types', $types);
        }

        return $query
            ->getQuery()
            ->enableResultCache(3600)
            ->getResult();
    }
}
