<?php

declare(strict_types=1);

namespace App\Doctrine;

use App\Utils\Creator\SmartAccessDecorator;
use Doctrine\ORM\Decorator\EntityManagerDecorator as DoctrineEntityManagerDecorator;
use Override;

class EntityManagerDecorator extends DoctrineEntityManagerDecorator
{
    #[Override]
    public function persist(object $object): void
    {
        if ($object instanceof SmartAccessDecorator) {
            $object = $object->getCreator();
        }

        parent::persist($object);
    }

    #[Override]
    public function remove(object $object): void
    {
        if ($object instanceof SmartAccessDecorator) {
            $object = $object->getCreator();
        }

        parent::remove($object);
    }

    #[Override]
    public function isUninitializedObject(mixed $value): bool
    {
        if ($value instanceof SmartAccessDecorator) {
            $value = $value->getCreator();
        }

        return $this->wrapped->isUninitializedObject($value);
    }
}
