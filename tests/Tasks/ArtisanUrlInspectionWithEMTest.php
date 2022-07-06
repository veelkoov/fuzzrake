<?php

declare(strict_types=1);

namespace App\Tests\Tasks;

use App\Entity\Artisan as ArtisanE;
use App\Entity\ArtisanUrl;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tasks\ArtisanUrlInspection;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Web\HttpClient\GentleHttpClient;
use App\Utils\Web\WebpageSnapshot\Cache;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ArtisanUrlInspectionWithEMTest extends KernelTestCaseWithEM
{
    public function testInspect(): void
    {
        self::bootKernel();

        $createdArtisan = $this->getTestArtisanWithArtisanUrl();
        self::getEM()->persist($createdArtisan);
        self::flush();

        self::assertCount(1, $createdArtisan->getUrls());
        $createdUrl = $createdArtisan->getUrls()[0];
        self::assertNotNull($createdUrl);
        self::assertNull($createdUrl->getState()->getLastFailureUtc());
        self::assertNull($createdUrl->getState()->getLastSuccessUtc());

        $task = new ArtisanUrlInspection(self::getEM()->getRepository(ArtisanUrl::class), $this->getTestWebpageSnapshotManager(), $this->getTestSymfonyStyle());
        $task->inspect(1);
        self::flush();

        $repo = self::getEM()->getRepository(ArtisanE::class);
        $retrievedArtisan = $repo->findAll()[0];

        self::assertCount(1, $retrievedArtisan->getUrls());
        $retrievedUrl = $retrievedArtisan->getUrls()[0];
        self::assertNotNull($retrievedUrl);
        self::assertNull($retrievedUrl->getState()->getLastFailureUtc(), 'Should not have failed');
        self::assertNotNull($retrievedUrl->getState()->getLastSuccessUtc(), 'Should have succeeded');
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

    private function getTestWebpageSnapshotCache(): Cache
    {
        return $this->createMock(Cache::class);
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
