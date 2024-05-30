<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters\ValueChecker;

interface ValueCheckerInterface
{
    /**
     * @param list<string> $items
     */
    public function matches(array $items, ?bool $matchedOther): bool;
}
