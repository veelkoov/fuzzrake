<?php

declare(strict_types=1);

namespace App\Photos\MiniaturesFinder;

use App\Utils\Web\HttpClient\GenericHttpClient;
use App\Utils\Web\Url;

class MiniaturesFinder
{
    private readonly FurtrackMiniatureUrlResolver $furtrackResolver;
    private readonly ScritchMiniatureUrlResolver $scritchResolver;

    public function __construct(
        GenericHttpClient $client,
    ) {
        $this->furtrackResolver = new FurtrackMiniatureUrlResolver($client);
        $this->scritchResolver = new ScritchMiniatureUrlResolver($client);
    }

    public function getMiniatureUrl(Url $photoUrl): string
    {
        if ($this->furtrackResolver->supports($photoUrl->getUrl())) {
            return $this->furtrackResolver->getMiniatureUrl($photoUrl);
        }

        if ($this->scritchResolver->supports($photoUrl->getUrl())) {
            return $this->scritchResolver->getMiniatureUrl($photoUrl);
        }

        throw new MiniatureFinderException("Unsupported URL: {$photoUrl->getUrl()}.");
    }

    /**
     * @param array<Url> $photoUrls
     */
    public function supportsAll(array $photoUrls): bool
    {
        return array_all($photoUrls, fn (Url $url) => $this->supports($url));
    }

    private function supports(Url $url): bool
    {
        return $this->furtrackResolver->supports($url->getUrl()) || $this->scritchResolver->supports($url->getUrl());
    }
}
