<?php

declare(strict_types=1);

namespace App\Filtering\RequestsHandling;

use App\Filtering\Consts;
use Doctrine\ORM\QueryBuilder;
use Veelkoov\Debris\Sets\StringSet;

readonly class SingleColumnSingleValueFilter
{
    public function __construct(
        private string $columnRef,
        private bool $nullable,
    ) {
    }

    public function applyChoicesTo(StringSet $selected, QueryBuilder $builder): void
    {
        if ($selected->isEmpty()) {
            return;
        }

        $values = QueryBuilderUtils::getUniqueId();
        $conditions = ["$this->columnRef IN (:$values)"];

        if (!$this->nullable) {
            $selected = $selected->map(static fn($value) => Consts::FILTER_VALUE_UNKNOWN === $value ? Consts::DATA_VALUE_UNKNOWN : $value);
        } elseif ($selected->contains(Consts::FILTER_VALUE_UNKNOWN)) {
            $conditions[] = "$this->columnRef IS NULL";
            $selected = $selected->minus(Consts::FILTER_VALUE_UNKNOWN);
        }

        $builder->setParameter($values, $selected);
        QueryBuilderUtils::andWhere($builder, $conditions);
    }
}

