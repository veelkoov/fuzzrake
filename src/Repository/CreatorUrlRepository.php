<?php

declare(strict_types=1);

namespace App\Repository;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\FieldsList;
use App\Entity\CreatorUrl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CreatorUrl|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreatorUrl|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreatorUrl[]    findAll()
 * @method CreatorUrl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<CreatorUrl>
 */
class CreatorUrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreatorUrl::class);
    }

    /**
     * @return CreatorUrl[]
     */
    public function getOrderedBySuccessDate(FieldsList $excluded): array
    {
        $builder = $this->createQueryBuilder('d_cu')
            ->leftJoin('d_cu.state', 'd_cus')
            ->join('d_cu.creator', 'd_c')
            ->leftJoin('d_c.volatileData', 'd_cvd')
            ->leftJoin('d_c.privateData', 'd_cpd')
            ->leftJoin('d_c.creatorIds', 'd_ci')
            ->addSelect('d_c')
            ->addSelect('d_cvd')
            ->addSelect('d_cpd')
            ->addSelect('d_cus')
            ->addSelect('d_ci')
            ->orderBy('d_cus.lastSuccessUtc', 'ASC')
            ->addOrderBy('d_cus.lastFailureUtc', 'ASC');

        if (!$excluded->isEmpty()) {
            $builder
                ->where('d_cu.type NOT IN (:excluded)')
                ->setParameter('excluded', $excluded->names());
        }

        $resultData = $builder->getQuery()->execute();

        return $resultData; // @phpstan-ignore return.type (Lack of skill to fix this)
    }

    /**
     * @return list<int>
     */
    public function getIdsOfActiveCreatorsHavingAnyTrackedUrl(): array
    {
        $result = $this->createQueryBuilder('d_cu')
            ->select('DISTINCT d_c.id')
            ->where('d_cu.type = :type')
            ->setParameter('type', Field::URL_COMMISSIONS->value)
            ->join('d_cu.creator', 'd_c')
            ->andWhere('d_c.inactiveReason = :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleColumnResult();

        return $result; // @phpstan-ignore return.type (Lack of skill to fix this)
    }
}
