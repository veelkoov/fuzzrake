<?php

declare(strict_types=1);

namespace App\Utils;

interface FieldReadInterface
{
    public function get(ArtisanField $field);
}
