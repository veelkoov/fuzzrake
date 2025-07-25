<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Http;

use Veelkoov\Debris\Maps\StringToString;

final readonly class ExpectedHttpCall
{
    public function __construct(
        public string $method,
        public string $url,
        public ?string $requestBody = null,
        public StringToString $requestHeaders = new StringToString(), // grep-code-debris-needs-improvements StringToStringList
        public int $responseCode = 200,
        public string $responseBody = '',
        public StringToString $responseHeaders = new StringToString(),
    ) {
    }
}
