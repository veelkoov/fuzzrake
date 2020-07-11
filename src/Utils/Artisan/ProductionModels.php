<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

class ProductionModels extends Dictionary
{
    public function getAttributeKey(): string
    {
        return 'production_models';
    }
}
