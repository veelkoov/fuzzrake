<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Http;

use Veelkoov\Debris\StringStringMap;

final readonly class ExpectedHttpCall
{
    public function __construct(
        public string $method,
        public string $url,
        public ?string $requestBody = null,
        public StringStringMap $requestHeaders = new StringStringMap(),
        public int $responseCode = 200,
        public string $responseBody = '',
        public StringStringMap $responseHeaders = new StringStringMap(),
    ) {
    }
}
