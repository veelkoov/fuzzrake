<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

class Features extends Dictionary
{
    public function getAttributeKey(): string
    {
        return 'features';
    }
}
