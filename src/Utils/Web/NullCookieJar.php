<?php

declare(strict_types=1);

namespace App\Utils\Web;

class NullCookieJar implements CookieJarInterface
{
    public function setupFor($curlHandle): void
    {
    }
}
