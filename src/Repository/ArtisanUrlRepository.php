<?php

declare(strict_types=1);

namespace App\Repository;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\FieldsList;
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
     * @return ArtisanUrl[]
     */
    public function getLeastRecentFetched(int $limit, FieldsList $excluded): array
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(ArtisanUrl::class, 'u');
        $rsm->addJoinedEntityFromClassMetadata(ArtisanUrlState::class, 'us', 'u',
            'state', ['id' => 's_id']);

        $whereClause = $excluded->empty() ? '' :
            'WHERE u.type NOT IN ('.implode(',', array_map(fn (Field $type): string => $this->getEntityManager()->getConnection()->quote($type->name, ParameterType::STRING), $excluded->names())).')';

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
                    LIMIT $limit
                ", $rsm)
            ->execute();
    }

    public function getOrderedBySuccessDate(FieldsList $excluded): array
    {
        $builder = $this->createQueryBuilder('u')
            ->join('u.artisan', 'a')
            ->join('a.volatileData', 'avd')
            ->join('a.privateData', 'apd')
            ->leftJoin('u.state', 'us')
            ->addSelect('a')
            ->addSelect('avd')
            ->addSelect('apd')
            ->addSelect('us')
            ->orderBy('us.lastSuccess', 'ASC')
            ->addOrderBy('us.lastFailure', 'ASC');

        if (!$excluded->empty()) {
            $builder
                ->where('u.type NOT IN (:excluded)')
                ->setParameter('excluded', $excluded->names());
        }

        return $builder->getQuery()->execute();
    }
}
