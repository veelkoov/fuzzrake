<?php

declare(strict_types=1);

namespace App\Doctrine;

use App\Utils\Artisan\SmartAccessDecorator;
use Doctrine\ORM\Decorator\EntityManagerDecorator as DoctrineEntityManagerDecorator;

class EntityManagerDecorator extends DoctrineEntityManagerDecorator
{
    public function persist($object)
    {
        if ($object instanceof SmartAccessDecorator) {
            $object = $object->getArtisan();
        }

        parent::persist($object);
    }
}
