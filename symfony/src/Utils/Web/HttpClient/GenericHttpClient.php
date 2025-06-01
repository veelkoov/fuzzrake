<?php

declare(strict_types=1);

namespace App\Utils\Web\HttpClient;

use App\Utils\DateTime\UtcClock;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Snapshots\SnapshotMetadata;
use App\Utils\Web\Url;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\BrowserKit\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;
use Veelkoov\Debris\StringStringMap;

final class GenericHttpClient implements HttpClientInterface
{
    private const string HEADER_USER_AGENT = 'Mozilla/5.0 (compatible; getfursu.it_bot/0.11; +https://getfursu.it/)';
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

        $allHeaders = $addHeaders
            ->plus('User-Agent', self::HEADER_USER_AGENT)
            ->mapKeys(static fn (string $headerName) => "HTTP_$headerName");
        $server = [...$allHeaders]; // grep-code-debris-needs-improvements

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
    public function getSingleCookieValue(string $cookieName, string $domain): ?string
    {
        return $this->browser->getCookieJar()->get($cookieName, domain: $domain)?->getValue();
    }
}
