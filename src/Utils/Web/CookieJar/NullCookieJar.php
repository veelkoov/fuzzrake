<?php

declare(strict_types=1);

namespace App\Utils\Web\CookieJar;

class NullCookieJar implements CookieJarInterface
{
    public function setupFor($curlHandle): void
    {
    }
}
