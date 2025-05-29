<?php

declare(strict_types=1);

namespace App\Utils\Web\HttpClient;

use App\Utils\DateTime\UtcClock;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Snapshots\SnapshotMetadata;
use App\Utils\Web\Url;
use Nette\Http\Url as NetteUrl;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\BrowserKit\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;
use Veelkoov\Debris\StringStringMap;

class GenericHttpClient implements HttpClientInterface
{
    private readonly HttpBrowser $browser;

    public function __construct(
        private readonly LoggerInterface $logger,
        SymfonyHttpClientInterface $httpClient,
    ) {
        $this->browser = new HttpBrowser($httpClient);
    }

    #[Override]
    public function fetch(Url $url, string $method = 'GET', StringStringMap $addHeaders = new StringStringMap(), ?string $content = null): Snapshot
    {
        $this->logger->info("Retrieving: '{$url->getUrl()}'");

        $server = [...$addHeaders->mapKeys(static fn (string $headerName) => "HTTP_$headerName")];

        $this->browser->request($method, $url->getUrl(), server: $server, content: $content);
        $response = $this->browser->getInternalResponse();

        $this->logger->info("Got response: '{$url->getUrl()}'");

        $contents = $response->getContent();
        $headers = $response->getHeaders();
        $httpCode = $this->correctHttpCode($url, $contents, $response);

        $errors = [];

        if (200 !== $httpCode) {
            $this->logger->info("Non-200 HTTP code ($httpCode): '{$url->getUrl()}'.");

            $errors[] = "HTTP status code $httpCode.";
        }

        if ([] === $errors) {
            $url->recordSuccessfulFetch();
        } else {
            $url->recordFailedFetch($httpCode, implode(' / ', $errors));
        }

        $metadata = new SnapshotMetadata(
            $url->getUrl(),
            UtcClock::now(),
            $httpCode,
            $headers, // @phpstan-ignore argument.type (Insufficient typehinting)
            $errors,
        );

        return new Snapshot($contents, $metadata);
    }

    private function correctHttpCode(Url $url, string $contents, Response $response): int
    {
        $originalCode = $response->getStatusCode();

        // TODO: Implement correction

        return $originalCode;
    }

    #[Override]
    public function getSingleCookieValue(string $url, string $cookieName): ?string
    {
        return $this->browser->getCookieJar()->get($cookieName, domain: (new NetteUrl($url))->getDomain())?->getValue();
    }
}
