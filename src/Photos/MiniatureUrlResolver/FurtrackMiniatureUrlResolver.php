<?php

declare(strict_types=1);

namespace App\Photos\MiniatureUrlResolver;

use App\Photos\MiniaturesUpdateException;
use App\Utils\Regexp\Pattern;
use App\Utils\Web\HttpClient\GentleHttpClient;
use App\Utils\Web\HttpClient\HttpClientInterface;
use App\Utils\Web\Url\FreeUrl;
use App\Utils\Web\Url\Url;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class FurtrackMiniatureUrlResolver implements MiniatureUrlResolver
{
    private readonly Pattern $pattern;

    public function __construct(
        #[Autowire(service: GentleHttpClient::class)]
        private readonly HttpClientInterface $httpClient,
    ) {
        $this->pattern = new Pattern('^https://www\.furtrack\.com/p/(?<pictureId>\d+)$');
    }

    #[Override]
    public function supports(string $url): bool
    {
        return $this->pattern->isMatch($url);
    }

    #[Override]
    public function getMiniatureUrl(Url $url): string
    {
        $pictureId = $this->pattern->strictMatch($url->getUrl())->matches['pictureId'];
        $miniatureUrl = "https://orca2.furtrack.com/thumb/$pictureId.jpg";

        $response = $this->httpClient->fetch(new FreeUrl($miniatureUrl, $url->getCreatorId()), 'HEAD');

        if (200 !== $response->metadata->httpCode) {
            throw new MiniaturesUpdateException('Non-200 HTTP response code.');
        }

        return $miniatureUrl;
    }
}
