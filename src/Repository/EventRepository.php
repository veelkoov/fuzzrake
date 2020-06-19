<?php

declare(strict_types=1);

namespace App\Repository;

use App\Doctrine\Hydrators\ColumnHydrator;
use App\Entity\Event;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
    public function selectTrackingTmpFailures(DateTimeInterface $date1, DateTimeInterface $date2): array
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
            ->setParameters(['date1' => $date1, 'date2' => $date2])
            ->select('en.id')
            ->getQuery()
            ->enableResultCache(3600)
            ->getResult(ColumnHydrator::COLUMN_HYDRATOR);

        return $this->findBy(['id' => $ids]);
    }

    /**
     * @return Event[]
     *
     * @throws DateTimeException
     */
    public function getRecent(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.timestamp >= :oldest')
            ->orderBy('e.timestamp', 'DESC')
            ->setParameter('oldest', DateTimeUtils::getUtcAt('-31 days'))
            ->getQuery()
            ->enableResultCache(3600)
            ->getResult();
    }
}
