<?php

declare(strict_types=1);

namespace App\Doctrine;

use App\Utils\Artisan\SmartAccessDecorator;
use Doctrine\ORM\Decorator\EntityManagerDecorator as DoctrineEntityManagerDecorator;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;

class EntityManagerDecorator extends DoctrineEntityManagerDecorator
{
    /**
     * @throws ORMInvalidArgumentException
     * @throws ORMException
     * @noinspection PhpDocRedundantThrowsInspection ORMException is thrown by decorated class, not the interface
     */
    public function persist($object)
    {
        if ($object instanceof SmartAccessDecorator) {
            $object = $object->getArtisan();
        }

        parent::persist($object);
    }
}
