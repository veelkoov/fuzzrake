<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\KotlinData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<KotlinData>
 *
 * @method KotlinData|null find($id, $lockMode = null, $lockVersion = null)
 * @method KotlinData|null findOneBy(array $criteria, array $orderBy = null)
 * @method KotlinData[]    findAll()
 * @method KotlinData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KotlinDataRepository extends ServiceEntityRepository
{
    final public const string OOO_NOTICE = 'ooo-notice';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KotlinData::class);
    }

    public function getString(string $name, string $default = ''): string
    {
        $entities = $this->findBy(['name' => $name]);

        if (1 === count($entities)) {
            return $entities[0]->getData();
        }

        return $default;
    }
}
