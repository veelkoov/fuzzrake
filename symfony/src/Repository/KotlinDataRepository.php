<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\KotlinData;
use App\Utils\Json;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JsonException;

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
    final public const string SPECIES_FILTER = 'species-filter';
    final public const string OOO_NOTICE = 'ooo-notice';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KotlinData::class);
    }

    /**
     * @param array<mixed> $default
     *
     * @return array<mixed>
     */
    public function getArray(string $name, array $default = []): array
    {
        $entities = $this->findBy(['name' => $name]);

        if (1 === count($entities)) {
            try {
                $result = Json::decode($entities[0]->getData());

                if (is_array($result)) {
                    return $result;
                }
            } catch (JsonException) {
                // Will just return the default
            }
        }

        return $default;
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
