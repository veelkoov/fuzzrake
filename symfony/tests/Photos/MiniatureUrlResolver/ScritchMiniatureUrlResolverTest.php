<?php

declare(strict_types=1);

namespace App\Tests\Photos\MiniatureUrlResolver;

use App\Photos\MiniaturesUpdateException;
use App\Photos\MiniatureUrlResolver\ScritchMiniatureUrlResolver;
use App\Tests\TestUtils\Http\ExpectedHttpCall;
use App\Tests\TestUtils\Http\HttpClientMockTrait;
use App\Utils\Web\Url\FreeUrl;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Veelkoov\Debris\Maps\StringToString;

#[Small]
class ScritchMiniatureUrlResolverTest extends TestCase
{
    use HttpClientMockTrait;

    /**
     * @throws MiniaturesUpdateException
     */
    public function testSuccessfulResolve(): void
    {
        $subject = new ScritchMiniatureUrlResolver(self::getHttpClientMock(
            new ExpectedHttpCall(
                'GET',
                'https://scritch.es/',
                responseCode: 200,
                responseHeaders: new StringToString(['Set-Cookie' => 'csrf-token=%21%40%23%24%25%5E%26*%28%29; path=/; SameSite=Strict']),
            ),
            new ExpectedHttpCall(
                'POST',
                'https://scritch.es/graphql',
                '{"operationName": "Medium", "variables": {"id": "847486df-64fc-45a2-b74b-11fd87fe43ca"}, "query": "query Medium($id: ID!, $tagging: Boolean) { medium(id: $id, tagging: $tagging) { thumbnail } }"}',
                new StringToString(['authorization' => 'Scritcher !@#$%^&*()', 'X-CSRF-Token' => '!@#$%^&*()']),
                200,
                '{"data": {"medium": {"thumbnail": "https://storage.scritch.es/scritch/45fbfc5483674d20dfd4cf6a342ea6653bd70440/thumbnail_9989c527-725a-4e98-b916-004c7ed91716.jpeg"}}}',
            ),
            new ExpectedHttpCall(
                'POST',
                'https://scritch.es/graphql',
                '{"operationName": "Medium", "variables": {"id": "b4a47593-f0e2-43b4-bc74-df6b9c3f555f"}, "query": "query Medium($id: ID!, $tagging: Boolean) { medium(id: $id, tagging: $tagging) { thumbnail } }"}',
                new StringToString(['authorization' => 'Scritcher !@#$%^&*()', 'X-CSRF-Token' => '!@#$%^&*()']),
                200,
                '{"data": {"medium": {"thumbnail": "https://storage.scritch.es/scritch/2a8ff452966723efe44ac65db076778e299e6824/thumbnail_77263eca-0ac2-4446-b86d-1f1fe21569a6.jpeg"}}}',
            ),
        ));

        self::assertSame(
            'https://storage.scritch.es/scritch/45fbfc5483674d20dfd4cf6a342ea6653bd70440/thumbnail_9989c527-725a-4e98-b916-004c7ed91716.jpeg',
            $subject->getMiniatureUrl(new FreeUrl('https://scritch.es/pictures/847486df-64fc-45a2-b74b-11fd87fe43ca', '')),
        );
        self::assertSame(
            'https://storage.scritch.es/scritch/2a8ff452966723efe44ac65db076778e299e6824/thumbnail_77263eca-0ac2-4446-b86d-1f1fe21569a6.jpeg',
            $subject->getMiniatureUrl(new FreeUrl('https://scritch.es/pictures/b4a47593-f0e2-43b4-bc74-df6b9c3f555f', '')),
        );
    }

    /**
     * @throws MiniaturesUpdateException
     */
    public function testHandlingMissingCsrfTokenCookie(): void
    {
        $subject = new ScritchMiniatureUrlResolver(self::getHttpClientMock(
            new ExpectedHttpCall('GET', 'https://scritch.es/'),
        ));

        self::expectException(MiniaturesUpdateException::class);
        self::expectExceptionMessage('Missing csrf-token cookie.');
        $subject->getMiniatureUrl(new FreeUrl('https://scritch.es/pictures/b4a47593-f0e2-43b4-bc74-df6b9c3f555f', ''));
    }
}
