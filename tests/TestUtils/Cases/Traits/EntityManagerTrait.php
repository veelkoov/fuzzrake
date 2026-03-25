<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Data\Definitions\Ages;
use App\Entity\Creator as CreatorE;
use App\Entity\Event;
use App\Entity\User;
use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
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

    protected static function addSimpleCreator(): Creator
    {
        $creator = self::getCreator();

        self::persistAndFlush($creator);

        return $creator;
    }

    protected static function addSimpleGenericEvent(): Event
    {
        $event = new Event()
            ->setDescription('Test event')
        ;

        self::persistAndFlush($event);

        return $event;
    }

    protected static function getCreator(// TODO: Possibly move into UserCreator class
        string $name = 'Test creator',
        string $creatorId = 'TEST000',
        string $country = 'CZ',
        ?Ages $ages = null,
        ?bool $nsfwWebsite = null,
        ?bool $nsfwSocial = null,
        ?bool $doesNsfw = null,
        ?bool $worksWithMinors = null,
    ): Creator {
        $result = new Creator()
            ->setName($name)
            ->setCreatorId($creatorId)
            ->setCountry($country)
            ->setAges($ages)
            ->setNsfwWebsite($nsfwWebsite)
            ->setNsfwSocial($nsfwSocial)
            ->setDoesNsfw($doesNsfw)
            ->setWorksWithMinors($worksWithMinors)
        ;

        $result
            ->getVolatileData()
            ->setLastCsUpdate(UtcClock::now());

        return $result;
    }

    protected static function persistAndFlush(object ...$entities): void
    {
        self::persist(...$entities);
        self::flush();
    }

    protected static function persistAndFlushWithUsers(object ...$entities): void
    {
        self::persistAndFlush(...self::unpackAppendUsers($entities));
    }

    /**
     * @param object[] $entities
     * @return iterable<object>
     */
    protected static function unpackAppendUsers(array $entities): iterable
    {
        yield from $entities;

        foreach ($entities as $entity) {
            if ($entity instanceof CreatorE) {
                yield $entity->getUser();
            }

            if ($entity instanceof Creator) {
                yield $entity->entity->getUser();
            }
        }
    }

    protected static function persist(object ...$entities): void
    {
        foreach ($entities as $entity) {
            self::getEM()->persist($entity);
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
