<?php

declare(strict_types=1);

namespace App\Tests\Photos\MiniatureUrlResolver;

use App\Photos\MiniaturesUpdateException;
use App\Photos\MiniatureUrlResolver\FurtrackMiniatureUrlResolver;
use App\Tests\TestUtils\Http\ExpectedHttpCall;
use App\Tests\TestUtils\Http\HttpClientMockTrait;
use App\Utils\Web\FreeUrl;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class FurtrackMiniatureUrlResolverTest extends TestCase
{
    use HttpClientMockTrait;

    /**
     * @throws MiniaturesUpdateException
     */
    public function testSuccessfulResolve(): void
    {
        $subject = new FurtrackMiniatureUrlResolver(self::getHttpClientMock(
            new ExpectedHttpCall(
                'HEAD',
                'https://orca2.furtrack.com/thumb/49767.jpg',
            ),
            new ExpectedHttpCall(
                'HEAD',
                'https://orca2.furtrack.com/thumb/41933.jpg',
            ),
        ));

        self::assertSame(
            'https://orca2.furtrack.com/thumb/49767.jpg',
            $subject->getMiniatureUrl(new FreeUrl('https://www.furtrack.com/p/49767')),
        );
        self::assertSame(
            'https://orca2.furtrack.com/thumb/41933.jpg',
            $subject->getMiniatureUrl(new FreeUrl('https://www.furtrack.com/p/41933')),
        );
    }

    /**
     * @throws MiniaturesUpdateException
     */
    public function testNon200HttpResponse(): void
    {
        $subject = new FurtrackMiniatureUrlResolver(self::getHttpClientMock(
            new ExpectedHttpCall(
                'HEAD',
                'https://orca2.furtrack.com/thumb/49767.jpg',
                responseCode: 403,
            ),
        ));

        self::expectException(MiniaturesUpdateException::class);
        self::expectExceptionMessage('Non-200 HTTP response code.');
        $subject->getMiniatureUrl(new FreeUrl('https://www.furtrack.com/p/49767'));
    }
}
