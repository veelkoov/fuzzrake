<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArtisanVolatileData;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use DateTime;
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
    public function getLastCsUpdateTime(): DateTime
    {
        return DateTimeUtils::getUtcAt($this
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
    public function getLastBpUpdateTime(): DateTime
    {
        return DateTimeUtils::getUtcAt($this
            ->createQueryBuilder('avd')
            ->select('MAX(avd.lastBpUpdate)')
            ->getQuery()
            ->enableResultCache(3600)
            ->getSingleScalarResult());
    }

    public function getLastCsUpdateTimeAsString(): string
    {
        try {
            return $this->getLastCsUpdateTime()->format('Y-m-d H:i');
        } catch (DateTimeException | UnexpectedResultException) {
            return 'unknown/error';
        }
    }

    public function getLastBpUpdateTimeAsString(): string
    {
        try {
            return $this->getLastBpUpdateTime()->format('Y-m-d H:i');
        } catch (DateTimeException | UnexpectedResultException) {
            return 'unknown/error';
        }
    }
}