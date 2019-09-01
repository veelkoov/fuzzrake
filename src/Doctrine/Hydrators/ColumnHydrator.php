<?php

declare(strict_types=1);

namespace App\Doctrine\Hydrators;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use PDO;

/**
 * @see https://stackoverflow.com/a/31316925/583786
 */
class ColumnHydrator extends AbstractHydrator
{
    public const COLUMN_HYDRATOR = 'COLUMN_HYDRATOR';

    protected function hydrateAllData()
    {
        return $this->_stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
