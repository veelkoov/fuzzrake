<?php

declare(strict_types=1);

namespace App\Repository;

use App\DataDefinitions\Fields\Field;
use App\Entity\ArtisanCommissionsStatus;
use App\Utils\Arrays;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\ResultSetMapping;
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
     * @throws UnexpectedResultException
     */
    public function getCommissionsStats(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('open_for_anything', 'open_for_anything', 'integer');
        $rsm->addScalarResult('closed_for_anything', 'closed_for_anything', 'integer');
        $rsm->addScalarResult('successfully_tracked', 'successfully_tracked', 'integer');
        $rsm->addScalarResult('partially_tracked', 'partially_tracked', 'integer');
        $rsm->addScalarResult('tracking_failed', 'tracking_failed', 'integer');
        $rsm->addScalarResult('tracking_issues', 'tracking_issues', 'integer');
        $rsm->addScalarResult('tracked', 'tracked', 'integer');
        $rsm->addScalarResult('total', 'total', 'integer');

        return $this
            ->getEntityManager()
            ->createNativeQuery('

    SELECT SUM(open_for_anything)                                                              AS open_for_anything
         , SUM(closed_for_anything)                                                            AS closed_for_anything
         , SUM(is_tracked_0_or_1 * tracker_found_anything_0_or_1 * (1 - avd_cs_tracker_issue)) AS successfully_tracked
         , SUM(is_tracked_0_or_1 * tracker_found_anything_0_or_1 * avd_cs_tracker_issue)       AS partially_tracked
         , SUM(is_tracked_0_or_1 * (1 - tracker_found_anything_0_or_1) * avd_cs_tracker_issue) AS tracking_failed
         , SUM(avd_cs_tracker_issue)                                                           AS tracking_issues
         , SUM(is_tracked_0_or_1)                                                              AS tracked
         , SUM(1)                                                                              AS total
    FROM (
         SELECT COUNT(nullif(open_offers_count, 0))   AS open_for_anything
              , COUNT(nullif(closed_offers_count, 0)) AS closed_for_anything
              , min(1, max(COUNT(nullif(open_offers_count, 0)), COUNT(nullif(closed_offers_count, 0)), 0))
                                                      AS tracker_found_anything_0_or_1
              , avd_cs_tracker_issue
              , COUNT(tracking_url_or_null)           AS is_tracked_0_or_1
              , a_id
         FROM (
              SELECT COUNT(nullif(acs.is_open, :false))       AS open_offers_count
                   , COUNT(nullif(acs.is_open, :true))        AS closed_offers_count
                   , avd.cs_tracker_issue                     AS avd_cs_tracker_issue
                   , nullif(COALESCE(au_cst.url, \'\'), \'\') AS tracking_url_or_null
                   , a.id                                     AS a_id
              FROM artisans AS a
                       LEFT JOIN artisans_volatile_data AS avd
                                 ON a.id = avd.artisan_id
                       LEFT JOIN artisans_urls AS au_cst
                                 ON a.id = au_cst.artisan_id AND au_cst.type = :url_type
                       LEFT JOIN artisans_commissions_statuses AS acs
                                 ON a.id = acs.artisan_id
              WHERE a.inactive_reason = \'\'
              GROUP BY a.id
        )
        GROUP BY a_id
    )

            ', $rsm)
            ->setParameters([
                'false'    => false,
                'true'     => true,
                'url_type' => Field::URL_COMMISSIONS->name,
            ])
            ->enableResultCache(3600)
            ->getSingleResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function getDistinctWithOpenCount(): array
    {
        return Arrays::assoc($this->createQueryBuilder('acs')
            ->select('acs.offer')
            ->addSelect('SUM(acs.isOpen) AS openCount')
            ->groupBy('acs.offer')
            ->getQuery()
            ->enableResultCache(3600)
            ->getArrayResult(), 'offer', 'openCount');
    }
}
