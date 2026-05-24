<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Entity\Creator as CreatorE;
use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Exceptions\UncheckedException;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool as OrmSchemaTool;
use Veelkoov\Debris\Maps\Pair;
use Veelkoov\Debris\Maps\StringToInt;
use Veelkoov\Debris\Sets\StringSet;

trait EntityManagerTrait
{
    use ContainerTrait;

    private static ?StringSet $tableNames = null;

    protected static function getEM(): EntityManagerInterface
    {
        return self::getContainerService(EntityManagerInterface::class, 'doctrine.orm.default_entity_manager');
    }

    protected static function resetDB(): void
    {
        $entityManager = self::getEM();

        if (null !== self::$tableNames) {
            $connection = $entityManager->getConnection();

            try {
                foreach (self::$tableNames as $tableName) {
                    $connection->executeStatement("DELETE FROM $tableName");
                }
            } catch (Exception $exception) {
                throw new UncheckedException($exception);
            }

            $entityManager->clear();

            return;
        }

        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new OrmSchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->updateSchema($metadata);

        // Table names sorted by number of associations (relations) ascending, which in simple cases is enough
        // to avoid having to worry about cascading while clearing the tables one by one.
        self::$tableNames = StringToInt::mapFrom($metadata,
            static fn (ClassMetadata $entity) => [$entity->getTableName(), count($entity->getAssociationNames())])
            ->sorted(static fn (Pair $a, Pair $b) => $a->value - $b->value)
            ->getKeys();
    }

    protected static function getCreatorRepository(): CreatorRepository
    {
        return self::getEM()->getRepository(CreatorE::class);
    }

    protected static function findCreatorByCreatorId(string $creatorId): Creator
    {
        $creator = self::getCreatorRepository()->findOneBy(['creatorId' => $creatorId]);
        self::assertNotNull($creator);

        return Creator::wrap($creator);
    }

    protected static function persistAndFlush(object ...$entities): void
    {
        self::persist(...$entities);
        self::flush();
    }

    protected static function persist(object ...$entities): void
    {
        $entityManager = self::getEM();

        foreach ($entities as $entity) {
            $entityManager->persist($entity);

            if ($entity instanceof CreatorE || $entity instanceof Creator) {
                $entityManager->persist($entity->getUser());
            }
        }
    }

    protected static function flush(): void
    {
        self::getEM()->flush();
    }

    protected static function clear(): void
    {
        self::getEM()->clear();
    }

    protected static function flushAndClear(): void
    {
        self::flush();
        self::clear();
    }
}
