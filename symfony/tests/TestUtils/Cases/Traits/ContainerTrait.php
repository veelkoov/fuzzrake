<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use RuntimeException;

trait ContainerTrait
{
    /**
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return T
     */
    protected static function getContainerService(string $className, string $alias = ''): object
    {
        $result = self::getContainer()->get('' !== $alias ? $alias : $className);

        if (!$result instanceof $className) {
            throw new RuntimeException('Received service of wrong type.');
        }

        return $result;
    }
}
