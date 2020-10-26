<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Entity\Artisan;
use App\Entity\Event;
use App\Utils\DateTime\DateTimeUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use RuntimeException;

trait DbEnabledTestCaseTrait
{
    protected static EntityManager $entityManager;

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

    protected static function getArtisan(string $name = 'Test artisan', string $makerId = 'TEST000'): Artisan
    {
        return (new Artisan())
            ->setName($name)
            ->setMakerId($makerId)
            ->getCommissionsStatus()
            ->setLastChecked(DateTimeUtils::getNowUtc())
            ->getArtisan();
    }

    protected static function persistAndFlush(object ...$entities): void
    {
        try {
            foreach ($entities as $entity) {
                self::$entityManager->persist($entity);
            }

            self::$entityManager->flush();
        } catch (ORMException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
