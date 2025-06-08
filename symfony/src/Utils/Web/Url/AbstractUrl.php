<?php

declare(strict_types=1);

namespace App\Utils\Web\Url;

use App\Utils\Web\UrlStrategy\Strategies;
use App\Utils\Web\UrlStrategy\Strategy;
use Override;

abstract readonly class AbstractUrl implements Url
{
    public Strategy $strategy;

    public function __construct(
        public string $url,
        ?Strategy $strategy = null,
    ) {
        $this->strategy = $strategy ?? Strategies::getFor($this->url);
    }

    #[Override]
    public function getOriginalUrl(): string
    {
        return $this->url;
    }

    #[Override]
    public function getUrl(): string
    {
        return $this->url;
    }

    #[Override]
    public function getStrategy(): Strategy
    {
        return $this->strategy;
    }
}
