<?php

declare(strict_types=1);

namespace App\Utils\Web\Snapshots;

use App\Utils\Web\HttpClient\GentleHttpClient;
use App\Utils\Web\HttpClient\HttpClientInterface;
use App\Utils\Web\Url\Url;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class SnapshotsManager
{
    private readonly FileSystemPathProvider $pathProvider;

    public function __construct(
        private readonly SnapshotsSerializer $serializer,
        #[Autowire(service: GentleHttpClient::class)]
        private readonly HttpClientInterface $httpClient,
        #[Autowire(env: 'resolve:SNAPSHOTS_STORAGE_PATH')]
        private readonly string $storagePath,
    ) {
        try {
            new Filesystem()->mkdir($storagePath);
        } catch (IOException $exception) {
            throw new RuntimeException("Storage path '$storagePath' is not an existing directory.", previous: $exception);
        }

        $this->pathProvider = new FileSystemPathProvider();
    }

    public function get(Url $url, bool $refetch): Snapshot
    {
        $snapshotDirPath = "$this->storagePath/".$this->pathProvider->getSnapshotDirPath($url);

        if (!$refetch) {
            try {
                $result = $this->serializer->load($snapshotDirPath);

                if (null !== $result) {
                    return $result;
                }
            } catch (ExceptionInterface|IOException) {
                // Treat as a cache miss, refetch
            }
        }

        $snapshot = $this->httpClient->fetch($url);

        try {
            $this->serializer->save($snapshotDirPath, $snapshot);
        } catch (ExceptionInterface|IOException $exception) {
            throw new RuntimeException('Failed to serialize snapshot', previous: $exception);
        }

        return $snapshot;
    }
}
