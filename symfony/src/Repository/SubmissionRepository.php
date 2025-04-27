<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Submission;
use App\Utils\Pagination\ItemsPage;
use App\Utils\Pagination\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Submission>
 *
 * @method Submission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Submission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Submission[]    findAll()
 * @method Submission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
     * @throws NonUniqueResultException
     */
    public function findByStrId(string $strId): ?Submission
    {
        try {
            $result = $this->createQueryBuilder('d_s')
                ->where('d_s.strId = :strId')
                ->setParameter('strId', $strId)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException) {
            return null;
        }

        return $result; // @phpstan-ignore-line Lack of skill to fix this
    }

    /**
     * @return ItemsPage<Submission>
     */
    public function getPage(int $pageNumber): ItemsPage
    {
        do {
            $query = $this->createQueryBuilder('d_s')
                ->orderBy('d_s.id', 'DESC')
                ->setFirstResult(Pagination::getFirstIdx(Pagination::PAGE_SIZE, $pageNumber))
                ->setMaxResults(Pagination::PAGE_SIZE);

            $paginator = new Paginator($query, fetchJoinCollection: true);

            $pagesCount = Pagination::countPages($paginator, Pagination::PAGE_SIZE);
        } while ($pageNumber > $pagesCount);

        return new ItemsPage(
            array_values([...$paginator->getIterator()]),
            $paginator->count(),
            $pageNumber,
            $pagesCount,
        );
    }
}
