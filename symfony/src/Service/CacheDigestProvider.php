<?php

declare(strict_types=1);

namespace App\Service;

interface CacheDigestProvider
{
    public function getCacheDigest(): string;
}
