<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArtisanCommissionsStatus;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArtisanCommissionsStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtisanCommissionsStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtisanCommissionsStatus[]    findAll()
 * @method ArtisanCommissionsStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtisanCommissionsStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtisanCommissionsStatus::class);
    }

    /**
     * @throws DateTimeException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getLastCstUpdateTime(): DateTime
    {
        return DateTimeUtils::getUtcAt($this
            ->createQueryBuilder('s')
            ->select('MAX(s.lastChecked)')
            ->getQuery()
            ->enableResultCache(3600)
            ->getSingleScalarResult());
    }

    public function getLastCstUpdateTimeAsString(): string
    {
        try {
            return $this->getLastCstUpdateTime()->format('Y-m-d H:i');
        } catch (DateTimeException | UnexpectedResultException) {
            return 'unknown/error';
        }
    }
}
