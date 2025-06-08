<?php

declare(strict_types=1);

namespace App\Photos\MiniatureUrlResolver;

use App\Photos\MiniaturesUpdateException;
use App\Utils\Web\HttpClient\GenericHttpClient;
use App\Utils\Web\HttpClient\HttpClientInterface;
use App\Utils\Web\Url\FreeUrl;
use App\Utils\Web\Url\Url;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TRegx\CleanRegex\Pattern;

class FurtrackMiniatureUrlResolver implements MiniatureUrlResolver
{
    private readonly Pattern $pattern;

    public function __construct(
        #[Autowire(service: GenericHttpClient::class)]
        private readonly HttpClientInterface $httpClient,
    ) {
        $this->pattern = Pattern::of('^https://www\.furtrack\.com/p/(?<pictureId>\d+)$');
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
        $miniatureUrl = "https://orca2.furtrack.com/thumb/$pictureId.jpg";

        $response = $this->httpClient->fetch(new FreeUrl($miniatureUrl), 'HEAD');

        if (200 !== $response->metadata->httpCode) {
            throw new MiniaturesUpdateException('Non-200 HTTP response code.');
        }

        return $miniatureUrl;
    }
}
