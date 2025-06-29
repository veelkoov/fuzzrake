<?php

declare(strict_types=1);

namespace App\Utils\Web\HttpClient;

use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Url\Url;
use Veelkoov\Debris\StringStringMap;

interface HttpClientInterface
{
    public function fetch(
        Url $url,
        string $method = 'GET',
        StringStringMap $addHeaders = new StringStringMap(),
        ?string $content = null,
    ): Snapshot;

    public function getSingleCookieValue(string $cookieName, string $domain): ?string;
}
