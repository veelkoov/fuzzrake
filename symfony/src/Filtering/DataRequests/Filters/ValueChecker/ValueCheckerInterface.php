<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters\ValueChecker;

interface ValueCheckerInterface
{
    public function matches(string $items, ?bool $matchedOther): bool;
}
