<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

class Styles extends Dictionary
{
    public function getAttributeKey(): string
    {
        return 'styles';
    }
}
