<?php

declare(strict_types=1);

namespace App\Doctrine\Hydrators;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use LogicException;

/**
 * @see https://stackoverflow.com/a/31316925/583786
 */
class ColumnHydrator extends AbstractHydrator
{
    final public const COLUMN_HYDRATOR = 'COLUMN_HYDRATOR';

    /**
     * @return list<mixed>
     *
     * @throws Exception
     */
    protected function hydrateAllData(): array
    {
        return $this->stmt?->fetchFirstColumn() ?? throw new LogicException('Statement is null');
    }
}
