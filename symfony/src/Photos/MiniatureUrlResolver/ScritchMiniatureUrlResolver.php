<?php

declare(strict_types=1);

namespace App\Photos\MiniatureUrlResolver;

use App\Photos\MiniaturesUpdateException;
use App\Utils\Collections\ArrayReader;
use App\Utils\Json;
use App\Utils\Web\HttpClient\GentleHttpClient;
use App\Utils\Web\HttpClient\HttpClientInterface;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Url\FreeUrl;
use App\Utils\Web\Url\Url;
use InvalidArgumentException;
use JsonException;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TRegx\CleanRegex\Pattern;
use Veelkoov\Debris\Maps\StringToString;

class ScritchMiniatureUrlResolver implements MiniatureUrlResolver
{
    private readonly Pattern $pattern;

    public function __construct(
        #[Autowire(service: GentleHttpClient::class)]
        private readonly HttpClientInterface $httpClient,
    ) {
        $this->pattern = Pattern::of('^https://scritch\.es/pictures/(?<pictureId>[-a-f0-9]{36})$');
    }

    #[Override]
    public function supports(string $url): bool
    {
        return $this->pattern->test($url);
    }

    #[Override]
    public function getMiniatureUrl(Url $url): string
    {
        $response = $this->getResponseForPictureUrl($url);

        if (200 !== $response->metadata->httpCode) {
            throw new MiniaturesUpdateException('Non-200 HTTP response code.');
        }

        try {
            return ArrayReader::of(Json::decode($response->contents))
                ->getNonEmptyString('[data][medium][thumbnail]');
        } catch (InvalidArgumentException|JsonException $exception) {
            throw new MiniaturesUpdateException('Wrong JSON data.', previous: $exception);
        }
    }

    /**
     * @throws MiniaturesUpdateException
     */
    private function getResponseForPictureUrl(Url $url): Snapshot
    {
        $csrfToken = $this->getCsrfToken();
        $jsonPayload = $this->getGraphQlJsonPayload($this->getPictureIdFor($url));

        $headers = new StringToString([
            'Content-Type' => 'application/json',
            'X-CSRF-Token' => $csrfToken,
            'authorization' => "Scritcher $csrfToken",
        ]);

        return $this->httpClient->fetch(new FreeUrl('https://scritch.es/graphql', $url->getCreatorId()), 'POST', $headers, $jsonPayload);
    }

    private function getGraphQlJsonPayload(string $pictureId): string
    {
        return <<<GRAPHQL
            {
                "operationName": "Medium",
                "variables": {"id": "$pictureId"},
                "query": "query Medium(\$id: ID!, \$tagging: Boolean) {
                    medium(id: \$id, tagging: \$tagging) { thumbnail }
                }"
            }
        GRAPHQL;
    }

    /**
     * @throws MiniaturesUpdateException
     */
    private function getCsrfToken(): string
    {
        return $this->getOptionalCsrfToken() ?? $this->getFirstRequiredCsrfToken();
    }

    private function getOptionalCsrfToken(): ?string
    {
        return $this->httpClient->getSingleCookieValue('csrf-token', 'scritch.es');
    }

    /**
     * @throws MiniaturesUpdateException
     */
    private function getFirstRequiredCsrfToken(): string
    {
        $this->httpClient->fetch(new FreeUrl('https://scritch.es/', ''));

        return $this->getOptionalCsrfToken() ?? throw new MiniaturesUpdateException('Missing csrf-token cookie.');
    }

    private function getPictureIdFor(Url $url): string
    {
        return $this->pattern->match($url->getUrl())->first()->get('pictureId');
    }
}
