<?php

declare(strict_types=1);

namespace App\Repository;

use App\Doctrine\Hydrators\ColumnHydrator;
use App\Entity\Event;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
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
     * @return Event[]
     */
    public function selectTrackingTmpFailures(DateTimeImmutable $date1, DateTimeImmutable $date2): array // FIXME #93
    {
        $ids = $this->createQueryBuilder('en')
            ->join(Event::class, 'eo', Join::WITH, new Andx([
                    'en.artisanName = eo.artisanName',
                    'DATE(eo.timestamp) IN (:date1, :date2)',
                    'DATE(en.timestamp) IN (:date1, :date2)',
                        new Orx([
                            new Andx(['eo.newStatus IS NULL', 'en.oldStatus IS NULL', 'eo.oldStatus = en.newStatus']),
                            new Andx(['en.newStatus IS NULL', 'eo.oldStatus IS NULL', 'en.oldStatus = eo.newStatus']),
                        ]),
                ])
            )
            ->setParameter('date1', $date1, Types::DATE_MUTABLE)
            ->setParameter('date2', $date2, Types::DATE_MUTABLE)
            ->select('en.id')
            ->getQuery()
            ->getResult(ColumnHydrator::COLUMN_HYDRATOR);

        return $this->findBy(['id' => $ids]);
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
