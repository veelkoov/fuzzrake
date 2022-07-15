<?php

declare(strict_types=1);

namespace App\Utils\Web\WebpageSnapshot;

use App\Utils\DateTime\DateTimeException;
use App\Utils\Traits\UtilityClass;
use JsonException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;

use function Psl\File\read;

final class Jar
{
    use UtilityClass;

    private const PATH_META_FILE = 'meta_file';
    private const PATH_DATA_FILE = 'data_file';

    private static Filesystem $fs;

    /**
     * @throws JsonException
     * @throws DateTimeException
     * @throws InvalidArgumentException
     */
    public static function load(string $baseDir): Snapshot
    {
        $paths = self::getPaths($baseDir);

        $metadataJsonString = read($paths[self::PATH_META_FILE]);
        $contents = read($paths[self::PATH_DATA_FILE]);

        $metadata = Json::deserialize($metadataJsonString);
        $result = Snapshot::restore($contents, $metadata);

        for ($index = 0; $index < $metadata->childCount; ++$index) {
            $result->addChild(self::load(self::getChildDirPath($baseDir, $index)));
        }

        return $result;
    }

    /**
     * @throws JsonException
     */
    public static function dump(string $baseDir, Snapshot $snapshot): void
    {
        self::$fs ??= new Filesystem();
        self::$fs->mkdir($baseDir);

        $paths = self::getPaths($baseDir);
        self::$fs->dumpFile($paths[self::PATH_META_FILE], Json::serialize(Metadata::from($snapshot)));
        self::$fs->dumpFile($paths[self::PATH_DATA_FILE], $snapshot->contents);

        foreach ($snapshot->getChildren() as $index => $child) {
            self::dump(self::getChildDirPath($baseDir, $index), $child);
        }
    }

    /**
     * @return array{meta_file: non-empty-string, data_file: non-empty-string}
     */
    private static function getPaths(string $baseDir): array
    {
        return [
            self::PATH_META_FILE => $baseDir.'/metadata.json',
            self::PATH_DATA_FILE => $baseDir.'/contents.data',
        ];
    }

    private static function getChildDirPath(string $baseDir, int $childIndex): string
    {
        return "$baseDir/child.$childIndex";
    }
}
