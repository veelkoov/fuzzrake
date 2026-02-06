<?php

declare(strict_types=1);

namespace App\Filtering\RequestsHandling;

use App\Utils\Traits\UtilityClass;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\QueryBuilder;

final class QueryBuilderUtils
{
    use UtilityClass;

    private static int $uniqueIdIndex = 1;

    public static function getUniqueId(): string
    {
        return 'd_uid'.((string) self::$uniqueIdIndex++);
    }

    /**
     * @param list<Func|Comparison|string> $conditions
     */
    public static function andWhere(QueryBuilder $builder, array $conditions): void
    {
        if ([] === $conditions) {
            return;
        } elseif (1 === count($conditions)) {
            $condition = array_first($conditions);
        } else {
            $condition = $builder->expr()->orX(...$conditions);
        }

        $builder->andWhere($condition);
    }
}
