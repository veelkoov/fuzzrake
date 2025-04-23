<?php

declare(strict_types=1);

namespace App\Repository;

use App\Data\Definitions\Fields\FieldsList;
use App\Entity\ArtisanUrl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

        if (!$excluded->isEmpty()) {
            $builder
                ->where('au.type NOT IN (:excluded)')
                ->setParameter('excluded', $excluded->names());
        }

        $resultData = $builder->getQuery()->execute();

        return $resultData; // @phpstan-ignore-line Lack of skill to fix this
    }
}
