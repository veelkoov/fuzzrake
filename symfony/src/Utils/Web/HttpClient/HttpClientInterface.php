<?php

declare(strict_types=1);

namespace App\Utils\Web\HttpClient;

use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Url;
use Veelkoov\Debris\Maps\StringToString;

interface HttpClientInterface
{
    public function fetch(
        Url $url,
        string $method = 'GET',
        StringToString $addHeaders = new StringToString(),
        ?string $content = null,
    ): Snapshot;

    public function getSingleCookieValue(string $cookieName, string $domain): ?string;
}
