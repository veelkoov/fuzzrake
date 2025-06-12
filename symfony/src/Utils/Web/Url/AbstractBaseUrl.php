<?php

declare(strict_types=1);

namespace App\Utils\Web\Url;

use App\Utils\Web\UrlStrategy\Strategies;
use App\Utils\Web\UrlStrategy\Strategy;
use App\Utils\Web\UrlUtils;
use Override;

abstract readonly class AbstractBaseUrl implements Url
{
    private Strategy $strategy;
    private string $host;

    public function __construct(
        private string $url,
    ) {
        $this->strategy = Strategies::getFor($this->url);
        $this->host = UrlUtils::getHost($url);
    }

    #[Override]
    final public function getHost(): string
    {
        return $this->host;
    }

    #[Override]
    final public function getUrl(): string
    {
        return $this->url;
    }

    #[Override]
    final public function getStrategy(): Strategy
    {
        return $this->strategy;
    }
}
