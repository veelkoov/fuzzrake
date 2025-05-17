<?php

declare(strict_types=1);

namespace App\Utils\Web\Snapshots;

use App\Utils\Web\Url;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;

class SnapshotsManager
{
    private readonly FileSystemPathProvider $pathProvider;
    private readonly Filesystem $filesystem;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly string $storeDirPath,
        private readonly HttpClientInterface $httpClient = new CookieEagerHttpClient(new GentleHttpClient(new FastHttpClient())),
    ) {
        $this->filesystem = new Filesystem();
        $this->pathProvider = new FileSystemPathProvider();
    }

    public function get(Url $url, bool $refetch): Snapshot
    {
        $snapshotDirPath = "$this->storeDirPath/".$this->pathProvider->getSnapshotDirPath($url->getUrl());

        if (!$refetch) {
            try {
                return self::loadFrom($snapshotDirPath);
            } catch (IOException) {
                // OK
            }
        }

        $snapshot = $this->httpClient->fetch($url);
        $snapshot->saveTo($snapshotDirPath);

        return $snapshot;
    }

    public function saveTo(string $snapshotDirPath, Snapshot $snapshot): void
    {
        $this->filesystem->mkdir($snapshotDirPath);

        $this->filesystem->dumpFile(self::getContentsPath($snapshotDirPath), $snapshot->contents);

        $metadataJsonString = $this->serializer->serialize($snapshot->metadata, 'json');
        $this->filesystem->dumpFile(self::getMetadataPath($snapshotDirPath), $metadataJsonString);
    }

    public function loadFrom(string $snapshotDirPath): Snapshot
    {
        $contents = $this->loadContents($snapshotDirPath);
        $metadata = $this->loadMetadata($snapshotDirPath);

        return new Snapshot($contents, $metadata);
    }

    private function loadContents(string $snapshotDirPath): string
    {
        return $this->filesystem->readFile(self::getContentsPath($snapshotDirPath));
    }

    private function loadMetadata(string $snapshotDirPath): SnapshotMetadata
    {
        $jsonString = $this->filesystem->readFile(self::getMetadataPath($snapshotDirPath));

        return $this->serializer->deserialize($jsonString, SnapshotMetadata::class, 'json');
    }

    private static function getMetadataPath(string $snapshotDirPath): string
    {
        return "$snapshotDirPath/metadata.json";
    }

    private static function getContentsPath(string $snapshotDirPath): string
    {
        return "$snapshotDirPath/contents.data";
    }
}
