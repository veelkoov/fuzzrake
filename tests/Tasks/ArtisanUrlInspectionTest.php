<?php

declare(strict_types=1);

namespace App\Tests\Tasks;

use App\Entity\Artisan as ArtisanE;
use App\Entity\ArtisanUrl;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tasks\ArtisanUrlInspection;
use App\Tests\TestUtils\DbEnabledKernelTestCase;
use App\Utils\Web\HttpClient\GentleHttpClient;
use App\Utils\Web\Snapshot\WebpageSnapshotCache;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ArtisanUrlInspectionTest extends DbEnabledKernelTestCase
{
    public function testInspect(): void
    {
        self::bootKernel();

        $createdArtisan = $this->getTestArtisanWithArtisanUrl();
        self::getEM()->persist($createdArtisan);
        self::flush();

        self::assertCount(1, $createdArtisan->getUrls());
        self::assertNull($createdArtisan->getUrls()->first()->getState()->getLastFailure());
        self::assertNull($createdArtisan->getUrls()->first()->getState()->getLastSuccess());

        $task = new ArtisanUrlInspection(self::getEM()->getRepository(ArtisanUrl::class), $this->getTestWebpageSnapshotManager(), $this->getTestSymfonyStyle());
        $task->inspect(1);
        self::flush();

        $repo = self::getEM()->getRepository(ArtisanE::class);
        /** @var ArtisanRepository $repo */
        $retrievedArtisan = $repo->findAll()[0];

        self::assertCount(1, $retrievedArtisan->getUrls());
        self::assertNull($retrievedArtisan->getUrls()->first()->getState()->getLastFailure(), 'Should not have failed');
        self::assertNotNull($retrievedArtisan->getUrls()->first()->getState()->getLastSuccess(), 'Should have succeeded');
    }

    private function getTestSymfonyStyle(): SymfonyStyle
    {
        return $this->createMock(SymfonyStyle::class);
    }

    private function getTestWebpageSnapshotManager(): WebpageSnapshotManager
    {
        return new WebpageSnapshotManager($this->getTestGentleHttpClient(), $this->getTestWebpageSnapshotCache(), $this->getTestLogger());
    }

    private function getTestArtisanWithArtisanUrl(): ArtisanE
    {
        $result = new ArtisanE();
        $result->addUrl(new ArtisanUrl());

        return $result;
    }

    private function getTestLogger(): LoggerInterface
    {
        return $this->createMock(LoggerInterface::class);
    }

    private function getTestWebpageSnapshotCache(): WebpageSnapshotCache
    {
        return $this->createMock(WebpageSnapshotCache::class);
    }

    private function getTestGentleHttpClient(): GentleHttpClient
    {
        $result = $this->createMock(GentleHttpClient::class);
        $result->expects(self::once())->method('get')->willReturn($this->getTestResponse());

        return $result;
    }

    private function getTestResponse(): ResponseInterface
    {
        $result = $this->createMock(ResponseInterface::class);
        $result->expects(self::once())->method('getStatusCode')->willReturn(200);

        return $result;
    }
}
