<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArtisanUrl;
use App\Entity\ArtisanUrlState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArtisanUrl|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtisanUrl|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtisanUrl[]    findAll()
 * @method ArtisanUrl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtisanUrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtisanUrl::class);
    }

    /**
     * @param string[] $excludedTypes
     *
     * @return ArtisanUrl[]
     */
    public function getLeastRecentFetched(int $limit, array $excludedTypes): array
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(ArtisanUrl::class, 'u');
        $rsm->addJoinedEntityFromClassMetadata(ArtisanUrlState::class, 'us', 'u',
            'state', ['id' => 's_id']);

        $whereClause = empty($excludedTypes) ? '' :
            'WHERE u.type NOT IN ('.implode(',', array_map(function (string $item): string {
                return $this->getEntityManager()->getConnection()->quote($item, ParameterType::STRING);
            }, $excludedTypes)).')';

        return $this->getEntityManager()
            ->createNativeQuery("
                    SELECT {$rsm->generateSelectClause()}
                    FROM artisans_urls AS u
                    LEFT JOIN artisans_urls_states AS us ON us.artisan_url_id = u.id
                    $whereClause
                    ORDER BY MAX(
                        COALESCE(us.last_failure, '2020-01-01 00:00:00'),
                        COALESCE(us.last_success, '2020-01-01 00:00:00')
                    ) ASC
                    LIMIT {$limit}
                ", $rsm)
            ->execute();
    }

    public function getOrderedBySuccessDate(array $excludedTypes): array
    {
        $builder = $this->createQueryBuilder('u')
            ->join('u.artisan', 'a')
            ->join('a.commissionsStatus', 'acs')
            ->join('a.privateData', 'apd')
            ->leftJoin('u.state', 'us')
            ->addSelect('a')
            ->addSelect('acs')
            ->addSelect('apd')
            ->addSelect('us')
            ->orderBy('us.lastSuccess', 'ASC')
            ->addOrderBy('us.lastFailure', 'ASC');

        if (!empty($excludedTypes)) {
            $builder
                ->where('u.type NOT IN (:excluded)')
                ->setParameter('excluded', $excludedTypes);
        }

        return $builder->getQuery()->execute();
    }
}
