<?php

declare(strict_types=1);

namespace App\Utils\Web\HttpClient;

use App\Utils\Web\HostCallsQueue;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Url\Url;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Veelkoov\Debris\Maps\StringToString;

class GentleHttpClient implements HttpClientInterface
{
    private readonly HostCallsQueue $queue;

    public function __construct(
        #[Autowire(service: CookieEagerHttpClient::class)]
        private readonly HttpClientInterface $client,
    ) {
        $this->queue = new HostCallsQueue(5);
    }

    #[Override]
    public function fetch(Url $url, string $method = 'GET', StringToString $addHeaders = new StringToString(), ?string $content = null): Snapshot
    {
        return $this->queue->patiently($url, fn () => $this->client->fetch($url, $method, $addHeaders, $content));
    }

    #[Override]
    public function getSingleCookieValue(string $cookieName, string $domain): ?string
    {
        return $this->client->getSingleCookieValue($cookieName, $domain);
    }
}
