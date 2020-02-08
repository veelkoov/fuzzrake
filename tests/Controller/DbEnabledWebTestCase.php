<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Artisan;
use App\Entity\Event;
use App\Utils\DateTime\DateTimeUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\Mapping\ClassMetadata;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class DbEnabledWebTestCase extends WebTestCase
{
    protected static EntityManager $entityManager;
    private static SchemaTool $schemaTool;

    /**
     * @var ClassMetadata[]
     */
    private static array $metadata;

    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        $result = parent::createClient($options, $server);

        self::setUpDb();

        return $result;
    }

    protected static function setUpDb(): void
    {
        self::$entityManager = self::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');

        self::$metadata = self::$entityManager->getMetadataFactory()->getAllMetadata();
        self::$schemaTool = new SchemaTool(self::$entityManager);
        self::$schemaTool->dropSchema(self::$metadata);
        self::$schemaTool->updateSchema(self::$metadata);
    }

    protected static function addSimpleArtisan(): Artisan
    {
        $artisan = (new Artisan())
            ->setName('Test artisan')
            ->setMakerId('TEST000')
            ->getCommissionsStatus()
            ->setLastChecked(DateTimeUtils::getNowUtc())
            ->getArtisan()
        ;

        try {
            self::$entityManager->persist($artisan);
            self::$entityManager->flush();
        } catch (ORMException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $artisan;
    }

    protected static function addSimpleGenericEvent(): Event
    {
        $artisan = (new Event())
            ->setDescription('Test event')
        ;

        try {
            self::$entityManager->persist($artisan);
            self::$entityManager->flush();
        } catch (ORMException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $artisan;
    }
}
