<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Entity\Creator as CreatorE;
use App\Entity\Event;
use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\Password;
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

    protected static function getCreator(
        string $name = 'Test creator',
        string $creatorId = 'TEST000',
        string $country = 'CZ',
        string $password = '',
        ?ContactPermit $contactAllowed = null,
        ?Ages $ages = null,
        ?bool $nsfwWebsite = null,
        ?bool $nsfwSocial = null,
        ?bool $doesNsfw = null,
        ?bool $worksWithMinors = null,
        ?string $emailAddress = null,
    ): Creator {
        $result = new Creator()
            ->setName($name)
            ->setCreatorId($creatorId)
            ->setCountry($country);

        $result
            ->getVolatileData()
            ->setLastCsUpdate(UtcClock::now())
        ;

        if ('' !== $password) {
            $result->setPassword($password);
            Password::encryptOn($result);
        }

        if (null !== $emailAddress) {
            $result->setEmailAddress($emailAddress);
        }

        $result->setContactAllowed($contactAllowed);
        $result->setAges($ages);
        $result->setNsfwWebsite($nsfwWebsite);
        $result->setNsfwSocial($nsfwSocial);
        $result->setDoesNsfw($doesNsfw);
        $result->setWorksWithMinors($worksWithMinors);

        return $result;
    }

    protected static function persistAndFlush(object ...$entities): void
    {
        self::persist(...$entities);
        self::flush();
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
