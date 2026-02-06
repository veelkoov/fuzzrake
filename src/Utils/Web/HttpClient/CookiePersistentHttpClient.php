<?php

declare(strict_types=1);

namespace App\Utils\Web\HttpClient;

use App\Utils\Web\HttpClient\Utils\CookieJarPersistence;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Url\Url;
use Override;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Veelkoov\Debris\Maps\StringToString;

class CookiePersistentHttpClient implements HttpClientInterface
{
    private readonly CookieJarPersistence $cookiePersistence;

    public function __construct(
        #[Autowire(service: GenericHttpClient::class)]
        private readonly HttpClientInterface $client,
        #[Autowire(param: 'kernel.project_dir')]
        string $projectDirectory,
    ) {
        $this->cookiePersistence = new CookieJarPersistence(
            "$projectDirectory/var/http-client-cookie-jar.json",
            $this->client->getCookieJar(),
        );
    }

    #[Override]
    public function fetch(Url $url, string $method = 'GET', StringToString $addHeaders = new StringToString(), ?string $content = null): Snapshot
    {
        try {
            return $this->client->fetch($url, $method, $addHeaders, $content);
        } finally {
            $this->cookiePersistence->save();
        }
    }

    #[Override]
    public function getCookieJar(): CookieJar
    {
        return $this->client->getCookieJar();
    }

    #[Override]
    public function getSingleCookieValue(string $cookieName, string $domain): ?string
    {
        return $this->client->getSingleCookieValue($cookieName, $domain);
    }
}
