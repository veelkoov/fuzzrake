<?php

declare(strict_types=1);

namespace App\Photos\MiniaturesFinder;

use App\Utils\Web\FreeUrl;
use App\Utils\Web\HttpClient\HttpClientInterface;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Url;
use Override;
use TRegx\CleanRegex\Pattern;
use Veelkoov\Debris\StringStringMap;

class ScritchMiniatureUrlResolver implements MiniatureUrlResolver
{
    private readonly Pattern $pattern;

    public function __construct(
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
        $pictureId = $this->pattern->match($url->getUrl())->first()->get('pictureId');

        $response = $this->getResponseForPictureId($pictureId);

        //        if (response.metadata.httpCode != 200) {
        //            throw MiniatureUrlResolverException("Non-200 HTTP response code")
        //        }
        //
        //        try {
        //            return JsonNavigator(response.contents).getNonEmptyString("data/medium/thumbnail")
        //        } catch (exception: JsonException) {
        //            throw MiniatureUrlResolverException("Wrong JSON data", exception)
        //        }
        return ''; // FIXME
    }

    private function getResponseForPictureId(string $pictureId): Snapshot
    {
        $csrfToken = $this->getCsrfToken();
        $jsonPayload = $this->getGraphQlJsonPayload($pictureId);

        $headers = new StringStringMap([
            'Content-Type' => 'application/json',
            'X-CSRF-Token' => $csrfToken,
            'authorization' => "Scritcher $csrfToken",
        ]);

        return $this->httpClient->fetch(new FreeUrl('https://scritch.es/graphql'), 'POST', $headers, $jsonPayload);
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

    private function getCsrfToken(): string
    {
        return $this->getOptionalCsrfToken() ?? $this->getFirstRequiredCsrfToken();
    }

    private function getOptionalCsrfToken(): ?string
    {
        return $this->httpClient->getSingleCookieValue('https://scritch.es/', 'csrf-token');
    }

    private function getFirstRequiredCsrfToken(): string
    {
        $this->httpClient->fetch(new FreeUrl('https://scritch.es/'));

        return $this->getOptionalCsrfToken() ?? throw new MiniatureFinderException('Missing csrf-token cookie.');
    }
}
