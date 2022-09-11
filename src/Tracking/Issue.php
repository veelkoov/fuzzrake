<?php

declare(strict_types=1);

namespace App\Tracking;

use Exception;

class Issue
{
    public function __construct(
        public readonly string $description,
        public readonly ?string $offer = null,
        public readonly ?string $url = null,
        public readonly ?Exception $exception = null,
    ) {
    }

    /**
     * @return array<string, string|Exception>
     */
    public function toLogContext(): array
    {
        return array_filter([
            'offer'     => $this->offer,
            'url'       => $this->url,
            'exception' => $this->exception,
        ]);
    }
}
