<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider;

use App\Filtering\Choices;
use Psr\Cache\InvalidArgumentException;

interface FilteredInterface
{
    /**
     * @return array<array<string, psJsonFieldValue>>
     *
     * @throws InvalidArgumentException
     */
    public function getPublicDataFor(Choices $choices): array;
}
