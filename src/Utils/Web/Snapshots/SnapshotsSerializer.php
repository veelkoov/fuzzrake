<?php

declare(strict_types=1);

namespace App\Utils\Web\Snapshots;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class SnapshotsSerializer
{
    private readonly Filesystem $filesystem;

    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
        $this->filesystem = new Filesystem();
    }

    /**
     * @throws ExceptionInterface|IOException
     */
    public function save(string $snapshotDirPath, Snapshot $snapshot): void
    {
        $this->filesystem->mkdir($snapshotDirPath);

        $this->filesystem->dumpFile(self::getContentsPath($snapshotDirPath), $snapshot->contents);

        $metadataJsonString = $this->serializer->serialize($snapshot->metadata, 'json');
        $this->filesystem->dumpFile(self::getMetadataPath($snapshotDirPath), $metadataJsonString);
    }

    /**
     * @throws ExceptionInterface|IOException
     */
    public function load(string $snapshotDirPath): ?Snapshot
    {
        if (!$this->filesystem->exists($snapshotDirPath)) {
            return null;
        }

        $contents = $this->loadContents($snapshotDirPath);
        $metadata = $this->loadMetadata($snapshotDirPath);

        return new Snapshot($contents, $metadata);
    }

    /**
     * @throws IOException
     */
    private function loadContents(string $snapshotDirPath): string
    {
        return $this->filesystem->readFile(self::getContentsPath($snapshotDirPath));
    }

    /**
     * @throws ExceptionInterface|IOException
     */
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
