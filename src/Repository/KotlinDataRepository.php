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
    public const SPECIES_FILTER = 'species-filter';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KotlinData::class);
    }

    /**
     * @param array<mixed> $default
     * @return array<mixed>
     */
    public function get(string $name, array $default = []): array
    {
        $entities = $this->findBy(['name' => $name]);

        if (1 === count($entities)) {
            try {
                $result = Json::decode($entities[0]->getJson());

                if (is_array($result)) {
                    return $result;
                }
            } catch (JsonException) {
                // Will just return the default
            }
        }

        return $default;
    }
}
