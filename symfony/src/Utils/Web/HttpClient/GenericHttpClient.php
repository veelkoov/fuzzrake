<?php

declare(strict_types=1);

namespace App\Utils\Web\HttpClient;

use App\Utils\DateTime\UtcClock;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Snapshots\SnapshotMetadata;
use App\Utils\Web\Url\Url;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\BrowserKit\Exception\ExceptionInterface as BrowserKitExceptionInterface;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\BrowserKit\Response;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;
use Throwable;
use Veelkoov\Debris\StringStringMap;

class GenericHttpClient implements HttpClientInterface
{
    private const string HEADER_USER_AGENT = 'Mozilla/5.0 (compatible; getfursu.it_bot/0.11; +https://getfursu.it/)';
    private readonly HttpBrowser $browser;

    public function __construct(
        private readonly LoggerInterface $logger,
        SymfonyHttpClientInterface $httpClient,
    ) {
        $this->browser = new HttpBrowser($httpClient);
        $this->browser->setMaxRedirects(2);
    }

    #[Override]
    public function fetch(Url $url, string $method = 'GET', StringStringMap $addHeaders = new StringStringMap(), ?string $content = null): Snapshot
    {
        $this->logger->info("Retrieving: '{$url->getUrl()}'");

        $allHeaders = $addHeaders
            ->plus('User-Agent', self::HEADER_USER_AGENT)
            ->mapKeys(static fn (string $headerName) => "HTTP_$headerName");
        $server = [...$allHeaders]; // grep-code-debris-needs-improvements

        $errors = [];
        $response = null;

        try {
            $this->browser->request($method, $url->getUrl(), server: $server, content: $content);
            $this->logger->info("Got response: '{$url->getUrl()}'");
            $response = $this->browser->getInternalResponse();
        } catch (BrowserKitExceptionInterface|HttpClientExceptionInterface $exception) {
            $this->logger->info("Retrieval failed: '{$url->getUrl()}'", ['exception' => $exception]);
            $errors[] = $exception->getMessage();
        } catch (Throwable $exception) {
            $this->logger->info("RETRIEVAL FAILED A LOT: '{$url->getUrl()}'", ['exception' => $exception]);
            $errors[] = $exception->getMessage();
        }

        $contents = $response?->getContent() ?? '';
        $headers = $response?->getHeaders() ?? [];
        $httpCode = $this->correctHttpCode($url, $response?->getStatusCode() ?? 0, $contents);

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

    private function correctHttpCode(Url $url, int $statusCode, string $contents): int
    {
        $originalCode = $statusCode;

        // TODO: Implement correction
        // val originalCode = response.status.value
        // val correctedCode = url.getStrategy().getLatentCode(url, contents, originalCode)
        //
        // if (correctedCode != originalCode) {
        //     logger.info { "Correcting HTTP code from $originalCode to 404 for ${url.getUrl()}" }
        // }
        //
        // return correctedCode

        return $originalCode;
    }

    #[Override]
    public function getSingleCookieValue(string $cookieName, string $domain): ?string
    {
        return $this->browser->getCookieJar()->get($cookieName, domain: $domain)?->getValue();
    }
}
