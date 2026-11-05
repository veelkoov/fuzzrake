<?php

declare(strict_types=1);

namespace App\Repository;

use App\Data\Submission\Filter;
use App\Entity\Submission;
use App\Utils\Collections\StatusToInt;
use App\Utils\Pagination\ItemsPage;
use App\Utils\Pagination\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SortDirection;

/**
 * @extends ServiceEntityRepository<Submission>
 */
class SubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Submission::class);
    }

    public function add(Submission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ItemsPage<Submission>
     */
    public function getPage(Filter $filter, int $pageNumber): ItemsPage
    {
        $builder = $this->createQueryBuilder('d_s')->orderBy('d_s.id', SortDirection::Descending);
        if (null !== $filter->update) {
            $builder->andWhere('d_s.isUpdate = :isUpdate')->setParameter('isUpdate', $filter->update);
        }
        if ([] !== $filter->statuses) {
            $builder->andWhere('d_s.status in (:statuses)')->setParameter('statuses', $filter->statuses);
        }

        $page = Pagination::getExistingPage($builder->getQuery(), $pageNumber);

        return new ItemsPage(
            $page->getItems(),
            $page->getTotalCount(),
            $page->getPageNumber(),
            $page->getPageCount(),
        );
    }

    public function getStatusToCount(): StatusToInt
    {
        return StatusToInt::mapFrom(
            $this->createQueryBuilder('d_s')
                ->select('d_s.status, COUNT(d_s) AS count')
                ->groupBy('d_s.status')
                ->orderBy('count', SortDirection::Descending)
                ->getQuery()
                ->getArrayResult(),
            // @phpstan-ignore argument.type
            static fn (array $row, mixed $_) => [$row['status'], $row['count']],
        );
    }
}
