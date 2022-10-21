<?php

declare(strict_types=1);

namespace App\Tests\Tasks\Miniatures;

use App\Tasks\Miniatures\MiniaturesUpdater;
use App\Tasks\Miniatures\Queries\FurtrackQuery;
use App\Tasks\Miniatures\Queries\ScritchQuery;
use App\Tasks\Miniatures\UpdateResult;
use App\Tracking\Web\HttpClient\GentleHttpClient;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MiniaturesUpdaterTest extends TestCase
{
    public function testUnsupported(): void
    {
        $artisan = Artisan::new()
            ->setPhotoUrls(
                "https://www.furtrack.com/p/49767\n"
                ."https://www.furtrack.com/p/49767f\n"
                ."https://scritch.es/pictures/847486df-64fc-45a2-b74b-11fd87fe43ca\n"
                .'https://scritch.es/pictures/847486df-64fc-45a2-b74b-11fd87fe43caf'
            )
            ->setMiniatureUrls('should not be touched');

        $subject = $this->getMiniaturesUpdater($this->createMock(GentleHttpClient::class));

        self::assertEquals('Unsupported URLs: "https://www.furtrack.com/p/49767f", "https://scritch.es/pictures/847486df-64fc-45a2-b74b-11fd87fe43caf"', $subject->update($artisan));
        self::assertEquals('should not be touched', $artisan->getMiniatureUrls());
    }

    public function testScritch(): void
    {
        $artisan = Artisan::new()->setPhotoUrls(
            "https://scritch.es/pictures/847486df-64fc-45a2-b74b-11fd87fe43ca\n"
            .'https://scritch.es/pictures/25ae6f07-9855-445f-9c1d-a8c78166b81b'
        );

        $client = $this->createMock(GentleHttpClient::class);
        $subject = $this->getMiniaturesUpdater($client);

        $client->method('get')->withConsecutive(
            ['https://scritch.es/'],
        )->willReturnOnConsecutiveCalls(
            $this->response(null, true),
        );

        $client->method('post')->withConsecutive(
            ['https://scritch.es/graphql', self::anything()],
            ['https://scritch.es/graphql', self::anything()],
        )->willReturnOnConsecutiveCalls(
            $this->response('{"data":{"medium":{"thumbnail":"expected"}}}', true),
            $this->response('{"data":{"medium":{"thumbnail":"result"}}}', true),
        );

        self::assertEquals(UpdateResult::RETRIEVED, $subject->update($artisan));
        self::assertEquals("expected\nresult", $artisan->getMiniatureUrls());
    }

    public function testFurtrack(): void
    {
        $artisan = Artisan::new()->setPhotoUrls(
            "https://www.furtrack.com/p/41933\n"
            .'https://www.furtrack.com/p/49767'
        );

        $client = $this->createMock(GentleHttpClient::class);
        $subject = $this->getMiniaturesUpdater($client);

        $client->method('get')->withConsecutive(
            ['https://solar.furtrack.com/view/post/41933'],
            ['https://solar.furtrack.com/view/post/49767'],
        )->willReturnOnConsecutiveCalls(
            $this->response('{"post":{"postStub":"expected","metaFiletype":"jpg"}}'),
            $this->response('{"post":{"postStub":"result","metaFiletype":"png"}}'),
        );

        self::assertEquals(UpdateResult::RETRIEVED, $subject->update($artisan));
        self::assertEquals("https://orca.furtrack.com/gallery/thumb/expected.jpg\nhttps://orca.furtrack.com/gallery/thumb/result.png", $artisan->getMiniatureUrls());
    }

    public function testMixed(): void
    {
        $artisan = Artisan::new()->setPhotoUrls(
            "https://www.furtrack.com/p/41933\n"
            .'https://scritch.es/pictures/847486df-64fc-45a2-b74b-11fd87fe43ca'
        );

        $client = $this->createMock(GentleHttpClient::class);
        $subject = $this->getMiniaturesUpdater($client);

        $client->method('get')->withConsecutive(
            ['https://solar.furtrack.com/view/post/41933'],
            ['https://scritch.es/'],
        )->willReturnOnConsecutiveCalls(
            $this->response('{"post":{"postStub":"expected","metaFiletype":"jpg"}}'),
            $this->response(null, true),
        );

        $client->method('post')->withConsecutive(
            ['https://scritch.es/graphql', self::anything()],
        )->willReturnOnConsecutiveCalls(
            $this->response('{"data":{"medium":{"thumbnail":"result"}}}', true),
        );

        self::assertEquals(UpdateResult::RETRIEVED, $subject->update($artisan));
        self::assertEquals("https://orca.furtrack.com/gallery/thumb/expected.jpg\nresult", $artisan->getMiniatureUrls());
    }

    public function testClearing(): void
    {
        $artisan = Artisan::new()
            ->setPhotoUrls('')
            ->setMiniatureUrls('should be cleared');

        $subject = $this->getMiniaturesUpdater($this->createMock(GentleHttpClient::class));

        self::assertEquals(UpdateResult::CLEARED, $subject->update($artisan));
        self::assertEquals('', $artisan->getMiniatureUrls());
    }

    public function testEmptyNoChange(): void
    {
        $artisan = Artisan::new()
            ->setPhotoUrls('')
            ->setMiniatureUrls('');

        $subject = $this->getMiniaturesUpdater($this->createMock(GentleHttpClient::class));

        self::assertEquals(UpdateResult::NO_CHANGE, $subject->update($artisan));
        self::assertEquals('', $artisan->getMiniatureUrls());
    }

    public function testNonEmptyNoChange(): void
    {
        $artisan = Artisan::new()
            ->setPhotoUrls("abcdef\nqwerty")
            ->setMiniatureUrls("poiuy\nlkjhg");

        $subject = $this->getMiniaturesUpdater($this->createMock(GentleHttpClient::class));

        self::assertEquals(UpdateResult::NO_CHANGE, $subject->update($artisan));
        self::assertEquals("poiuy\nlkjhg", $artisan->getMiniatureUrls());
    }

    public function testUnexpectedResponse(): void
    {
        $artisan = Artisan::new()->setPhotoUrls('https://www.furtrack.com/p/41933');

        $client = $this->createMock(GentleHttpClient::class);
        $subject = $this->getMiniaturesUpdater($client);

        $client->method('get')->withAnyParameters()->willReturn($this->response('Imagine HTTP 500'));

        self::assertEquals('Details: Syntax error', $subject->update($artisan));
    }

    public function testWrongCsrfScritchCookie(): void
    {
        $artisan = Artisan::new()
            ->setPhotoUrls('https://scritch.es/pictures/847486df-64fc-45a2-b74b-11fd87fe43ca');

        $client = $this->createMock(GentleHttpClient::class);
        $subject = $this->getMiniaturesUpdater($client);

        $client->method('get')->withConsecutive(['https://scritch.es/'])
            ->willReturnOnConsecutiveCalls($this->response(null, false));

        self::assertEquals('Details: Missing csrf-token cookie', $subject->update($artisan));
    }

    private function getMiniaturesUpdater(GentleHttpClient $client): MiniaturesUpdater
    {
        return new MiniaturesUpdater(new ScritchQuery($client), new FurtrackQuery($client));
    }

    private function response(string $content = null, bool $setupScritchCookie = false): ResponseInterface&MockObject
    {
        $result = $this->createMock(ResponseInterface::class);
        if (null !== $content) {
            $result->expects(self::once())->method('getContent')->willReturn($content);
        }

        if ($setupScritchCookie) {
            $result->expects(self::once())->method('getHeaders')->willReturn([
                'set-cookie' => ['csrf-token=a-token; path=/'],
            ]);
        }

        return $result;
    }
}
