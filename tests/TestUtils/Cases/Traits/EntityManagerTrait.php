<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Entity\Creator as CreatorE;
use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool as OrmSchemaTool;

trait EntityManagerTrait
{
    use ContainerTrait;

    protected static function getEM(): EntityManagerInterface
    {
        return self::getContainerService(EntityManagerInterface::class, 'doctrine.orm.default_entity_manager');
    }

    protected static function resetDB(): void
    {
        $metadata = self::getEM()->getMetadataFactory()->getAllMetadata();

        $schemaTool = new OrmSchemaTool(self::getEM());
        $schemaTool->dropSchema($metadata);
        $schemaTool->updateSchema($metadata);
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
