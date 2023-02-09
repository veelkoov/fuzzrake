<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters;

use App\Utils\Artisan\SmartAccessDecorator as Artisan;

interface FilterInterface
{
    public function matches(Artisan $artisan): bool;
}
