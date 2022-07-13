<?php

declare(strict_types=1);

namespace App\Utils;

use InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface as Accessor;

class ArrayReader
{
    /**
     * @var array<mixed>
     */
    private array $data;

    private Accessor $propertyAccessor;

    /**
     * @param mixed $data When not array, will throw an exception
     */
    public function __construct(mixed $data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Expected array to traverse');
        }

        $this->data = $data;

        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
    }

    public function getNonEmptyString(string $propertyPath): string
    {
        $result = Enforce::string($this->propertyAccessor->getValue($this->data, $propertyPath));

        if ('' === $result) {
            throw new InvalidArgumentException('Retrieved an empty string');
        }

        return $result;
    }
}
