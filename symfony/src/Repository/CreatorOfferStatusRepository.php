<?php

declare(strict_types=1);

namespace App\Repository;

use App\Data\Definitions\Fields\Field;
use App\Entity\CreatorOfferStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;
use Veelkoov\Debris\StringIntMap;
use Veelkoov\Debris\StringSet;

/**
 * @method CreatorOfferStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreatorOfferStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreatorOfferStatus[]    findAll()
 * @method CreatorOfferStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<CreatorOfferStatus>
 */
class CreatorOfferStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreatorOfferStatus::class);
    }

    /**
     * @return array{
     *     open_for_anything: int,
     *     closed_for_anything: int,
     *     successfully_tracked: int,
     *     partially_tracked: int,
     *     tracking_failed: int,
     *     tracking_issues: int,
     *     tracked: int,
     *     total: int,
     * }
     *
     * @throws UnexpectedResultException
     */
    public function getOfferStatusStats(): array
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

        $result = $this
            ->getEntityManager()
            ->createNativeQuery('

    SELECT SUM(open_for_anything)                                                              AS open_for_anything
         , SUM(closed_for_anything)                                                            AS closed_for_anything
         , SUM(is_tracked_0_or_1 * tracker_found_anything_0_or_1 * (1 - cvd_cs_tracker_issue)) AS successfully_tracked
         , SUM(is_tracked_0_or_1 * tracker_found_anything_0_or_1 * cvd_cs_tracker_issue)       AS partially_tracked
         , SUM(is_tracked_0_or_1 * (1 - tracker_found_anything_0_or_1) * cvd_cs_tracker_issue) AS tracking_failed
         , SUM(cvd_cs_tracker_issue)                                                           AS tracking_issues
         , SUM(is_tracked_0_or_1)                                                              AS tracked
         , SUM(1)                                                                              AS total
    FROM (
         SELECT COUNT(nullif(open_offers_count, 0))   AS open_for_anything
              , COUNT(nullif(closed_offers_count, 0)) AS closed_for_anything
              , min(1, max(COUNT(nullif(open_offers_count, 0)), COUNT(nullif(closed_offers_count, 0)), 0))
                                                      AS tracker_found_anything_0_or_1
              , cvd_cs_tracker_issue
              , COUNT(tracking_url_or_null)           AS is_tracked_0_or_1
              , s_c_id
         FROM (
              SELECT COUNT(nullif(s_cos.is_open, :false))   AS open_offers_count
                   , COUNT(nullif(s_cos.is_open, :true))    AS closed_offers_count
                   , s_cvd.cs_tracker_issue                 AS cvd_cs_tracker_issue
                   , nullif(COALESCE(s_cu.url, \'\'), \'\') AS tracking_url_or_null
                   , s_c.id                                 AS s_c_id
              FROM creators AS s_c
                       LEFT JOIN creators_volatile_data AS s_cvd
                                 ON s_c.id = s_cvd.creator_id
                       LEFT JOIN creators_urls AS s_cu
                                 ON s_c.id = s_cu.creator_id AND s_cu.type = :url_type
                       LEFT JOIN creators_offers_statuses AS s_cos
                                 ON s_c.id = s_cos.creator_id
              WHERE s_c.inactive_reason = \'\'
              GROUP BY s_c.id
        )
        GROUP BY s_c_id
    )

            ', $rsm)
            ->setParameters([
                'false'    => false,
                'true'     => true,
                'url_type' => Field::URL_COMMISSIONS->value,
            ])
            ->getSingleResult(AbstractQuery::HYDRATE_ARRAY);

        return $result; // @phpstan-ignore-line Lack of skill to fix this
    }

    public function getDistinctWithOpenCount(): StringIntMap
    {
        $result = $this->createQueryBuilder('d_cos')
            ->select('d_cos.offer')
            ->addSelect('SUM(d_cos.isOpen) AS openCount')
            ->groupBy('d_cos.offer')
            ->getQuery()
            ->getArrayResult();

        return StringIntMap::fromRows($result, 'offer', 'openCount');
    }

    public function getDistinctOpenFor(): StringSet
    {
        $result = $this->createQueryBuilder('d_cos')
            ->select('DISTINCT d_cos.offer')
            ->where('d_cos.isOpen = :true')
            ->setParameter('true', true)
            ->getQuery()
            ->getSingleColumnResult();

        return new StringSet($result); // @phpstan-ignore argument.type (Lack of skill to fix this)
    }
}
