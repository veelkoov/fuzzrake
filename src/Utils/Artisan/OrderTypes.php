<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

class OrderTypes extends Dictionary
{
    public function getAttributeKey(): string
    {
        return 'order_types';
    }
}
