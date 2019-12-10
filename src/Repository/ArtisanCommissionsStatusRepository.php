<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArtisanCommissionsStatus;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ArtisanCommissionsStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtisanCommissionsStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtisanCommissionsStatus[]    findAll()
 * @method ArtisanCommissionsStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtisanCommissionsStatusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ArtisanCommissionsStatus::class);
    }

    /**
     * @throws DateTimeException
     * @throws NonUniqueResultException
     */
    public function getLastCstUpdateTime(): DateTime
    {
        return DateTimeUtils::getUtcAt($this
            ->createQueryBuilder('s')
            ->select('MAX(s.lastChecked)')
            ->getQuery()
            ->getSingleScalarResult());
    }

    public function getLastCstUpdateTimeAsString(): string
    {
        try {
            return $this->getLastCstUpdateTime()->format('Y-m-d H:i');
        } catch (DateTimeException | NonUniqueResultException $e) {
            return 'unknown/error';
        }
    }
}
