<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\DataDefinitions\Ages;
use App\Entity\Artisan as ArtisanE;
use App\Entity\Event;
use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DateTime\UtcClock;
use App\Utils\Password;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool as OrmSchemaTool;
use Exception;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

trait EntityManagerTrait
{
    private static ?EntityManagerInterface $entityManager = null;

    /**
     * @param array<string, string> $options
     */
    protected static function bootKernel(array $options = []): KernelInterface
    {
        $result = parent::bootKernel($options);

        self::$entityManager = null;
        self::resetDB();

        return $result;
    }

    protected static function getEM(): EntityManagerInterface
    {
        return self::$entityManager ??= self::retrieveEM(self::getContainer());
    }

    private static function retrieveEM(ContainerInterface $container): EntityManagerInterface
    {
        try {
            $entityManager = $container->get('doctrine.orm.default_entity_manager');
        } catch (Exception $caught) {
            throw new RuntimeException(previous: $caught);
        }

        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);

        return $entityManager;
    }

    protected static function resetDB(): void
    {
        $metadata = self::getEM()->getMetadataFactory()->getAllMetadata();

        $schemaTool = new OrmSchemaTool(self::getEM());
        $schemaTool->dropSchema($metadata);
        $schemaTool->updateSchema($metadata);
    }

    protected static function getArtisanRepository(): ArtisanRepository
    {
        return self::getEM()->getRepository(ArtisanE::class);
    }

    protected static function findArtisanByMakerId(string $makerId): Artisan
    {
        $artisan = self::getArtisanRepository()->findOneBy(['makerId' => $makerId]);
        self::assertNotNull($artisan);

        return Artisan::wrap($artisan);
    }

    protected static function addSimpleArtisan(): Artisan
    {
        $artisan = self::getArtisan();

        self::persistAndFlush($artisan);

        return $artisan;
    }

    protected static function addSimpleGenericEvent(): Event
    {
        $event = (new Event())
            ->setDescription('Test event')
        ;

        self::persistAndFlush($event);

        return $event;
    }

    protected static function getArtisan(
        string $name = 'Test artisan',
        string $makerId = 'TEST000',
        string $country = 'CZ',
        string $password = '',
        string $contactAllowed = '',
        ?Ages $ages = null,
        ?bool $nsfwWebsite = null,
        ?bool $nsfwSocial = null,
        ?bool $doesNsfw = null,
        ?bool $worksWithMinors = null,
    ): Artisan {
        $result = (new Artisan())
            ->setName($name)
            ->setMakerId($makerId)
            ->setCountry($country);

        $result
            ->getVolatileData()
            ->setLastCsUpdate(UtcClock::now())
        ;

        if ('' !== $password) {
            $result->setPassword($password);
            Password::encryptOn($result);
        }

        if ('' !== $contactAllowed) {
            $result->setContactAllowed($contactAllowed);
        }

        $result->setAges($ages);
        $result->setNsfwWebsite($nsfwWebsite);
        $result->setNsfwSocial($nsfwSocial);
        $result->setDoesNsfw($doesNsfw);
        $result->setWorksWithMinors($worksWithMinors);

        return $result;
    }

    protected static function persistAndFlush(object ...$entities): void
    {
        foreach ($entities as $entity) {
            self::getEM()->persist($entity);
        }

        self::flush();
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
