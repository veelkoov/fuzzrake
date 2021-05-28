<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\Utils\Traits\Singleton;
use DateTimeInterface;

class SchemaFixer
{
    use Singleton;

    public function fix(array $data, DateTimeInterface $timestamp): array
    {
        // Current schema version: 8

        // No fixes required at the moment, only schema v8 supported

        return $data;
    }
}
