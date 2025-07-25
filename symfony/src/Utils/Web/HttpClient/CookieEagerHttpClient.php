<?php

declare(strict_types=1);

namespace App\Utils\Web\HttpClient;

use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Url\Url;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Veelkoov\Debris\Maps\StringToString;
use Veelkoov\Debris\StringSet;

class CookieEagerHttpClient implements HttpClientInterface
{
    private readonly StringSet $prefetched;

    public function __construct(
        #[Autowire(service: GenericHttpClient::class)]
        private readonly HttpClientInterface $client,
    ) {
        $this->prefetched = new StringSet();
    }

    #[Override]
    public function fetch(Url $url, string $method = 'GET', StringToString $addHeaders = new StringToString(), ?string $content = null): Snapshot
    {
        $cookieInitUrl = $url->getStrategy()->getCookieInitUrl();

        if (null !== $cookieInitUrl && !$this->prefetched->contains($cookieInitUrl->getUrl())) {
            $this->client->fetch($cookieInitUrl);

            $this->prefetched->add($cookieInitUrl->getUrl());
        }

        return $this->client->fetch($url, $method, $addHeaders, $content);
    }

    #[Override]
    public function getSingleCookieValue(string $cookieName, string $domain): ?string
    {
        return $this->client->getSingleCookieValue($cookieName, $domain);
    }
}
