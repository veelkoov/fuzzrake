<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Http;

use App\Utils\Web\HttpClient\GenericHttpClient;
use App\Utils\Web\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

trait HttpClientMockTrait
{
    use ClockSensitiveTrait; // FIXME: Remove; grep-workaround-throttling

    public function getHttpClientMock(ExpectedHttpCall ...$expectedHttpCalls): HttpClientInterface
    {
        self::mockTime();

        $factory = function (string $method, string $url, array $options) use (&$expectedHttpCalls): MockResponse {
            $expected = array_shift($expectedHttpCalls);
            self::assertNotNull($expected);

            self::assertSame($expected->method, $method, 'Expected different HTTP method to be used.');
            self::assertSame($expected->url, $url, 'Expected different URL to be requested.');

            $headers = $options['headers'] ?? [];
            self::assertIsArray($headers);

            foreach ($expected->requestHeaders as $key => $value) {
                $expectedHeader = strtolower($key).": $value";

                self::assertContains($expectedHeader, $headers, 'Missing expected header in the request.');
            }

            return new MockResponse($expected->responseBody, [
                'http_code' => $expected->responseCode,
                'response_headers' => $expected->responseHeaders->toArray(),
            ]);
        };

        return new GenericHttpClient(
            self::createStub(LoggerInterface::class),
            new MockHttpClient($factory),
        );
    }
}
