<?php

declare(strict_types=1);

namespace App\Repository;

use App\DataDefinitions\Fields\FieldsList;
use App\Entity\Artisan;
use App\Entity\ArtisanPrivateData;
use App\Entity\ArtisanUrl;
use App\Entity\ArtisanUrlState;
use App\Entity\ArtisanVolatileData;
use App\Entity\MakerId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArtisanUrl|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtisanUrl|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtisanUrl[]    findAll()
 * @method ArtisanUrl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<ArtisanUrl>
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
        $rsm->addRootEntityFromClassMetadata(ArtisanUrl::class, 'au');
        $rsm->addJoinedEntityFromClassMetadata(ArtisanUrlState::class, 'us', 'au',
            'state', ['id' => 's_id']);
        $rsm->addJoinedEntityFromClassMetadata(Artisan::class, 'a', 'au',
            'artisan', ['id' => 'a_id']);
        $rsm->addJoinedEntityFromClassMetadata(ArtisanVolatileData::class, 'avd', 'a',
            'volatileData', ['id' => 'avd_id', 'artisan_id' => 'avd_artisan_id']);
        $rsm->addJoinedEntityFromClassMetadata(ArtisanPrivateData::class, 'apd', 'a',
            'privateData', ['id' => 'apd_id', 'artisan_id' => 'apd_artisan_id']);
        $rsm->addJoinedEntityFromClassMetadata(MakerId::class, 'mi', 'a',
            'makerIds', ['id' => 'mi_id', 'artisan_id' => 'mi_artisan_id', 'maker_id' => 'mi_maker_id']);

        $whereClause = $excluded->empty() ? '' :
            'WHERE au.type NOT IN ('.implode(',', array_map(fn (string $type): string => $this->getEntityManager()->getConnection()->quote($type, ParameterType::STRING), $excluded->names())).')';

        return $this->getEntityManager()
            ->createNativeQuery("
                    SELECT {$rsm->generateSelectClause()}
                    FROM artisans_urls AS au
                    LEFT JOIN artisans_urls_states AS us ON us.artisan_url_id = au.id
                    JOIN artisans AS a ON a.id = au.artisan_id
                    LEFT JOIN artisans_volatile_data AS avd ON avd.artisan_id = a.id
                    LEFT JOIN artisans_private_data AS apd ON apd.artisan_id = a.id
                    LEFT JOIN maker_ids AS mi ON mi.artisan_id = a.id
                    $whereClause
                    ORDER BY MAX(
                        COALESCE(us.last_failure, '2020-01-01 00:00:00'),
                        COALESCE(us.last_success, '2020-01-01 00:00:00')
                    ) ASC
                    LIMIT $limit
                ", $rsm)
            ->execute();
    }

    /**
     * @return ArtisanUrl[]
     */
    public function getOrderedBySuccessDate(FieldsList $excluded): array
    {
        $builder = $this->createQueryBuilder('au')
            ->leftJoin('au.state', 'us')
            ->join('au.artisan', 'a')
            ->leftJoin('a.volatileData', 'avd')
            ->leftJoin('a.privateData', 'apd')
            ->leftJoin('a.makerIds', 'mi')
            ->addSelect('a')
            ->addSelect('avd')
            ->addSelect('apd')
            ->addSelect('us')
            ->addSelect('mi')
            ->orderBy('us.lastSuccessUtc', 'ASC')
            ->addOrderBy('us.lastFailureUtc', 'ASC');

        if (!$excluded->empty()) {
            $builder
                ->where('au.type NOT IN (:excluded)')
                ->setParameter('excluded', $excluded->names());
        }

        return $builder->getQuery()->execute();
    }
}
