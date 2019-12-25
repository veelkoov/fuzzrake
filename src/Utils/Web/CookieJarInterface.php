<?php

declare(strict_types=1);

namespace App\Utils\Web;

interface CookieJarInterface
{
    public function setupFor($curlHandle): void;
}
