<?php

declare(strict_types=1);

namespace App\Utils\Web\Snapshot;

use App\Utils\DateTime\DateTimeException;
use App\Utils\Json;
use JsonException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;

final class WebpageSnapshotJar
{
    private const JSON_SERIALIZATION_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                                             | JSON_UNESCAPED_LINE_TERMINATORS | JSON_PRETTY_PRINT;
    private const PATH_META_FILE = 'meta_file';
    private const PATH_DATA_FILE = 'data_file';

    private static Filesystem $fs;

    /**
     * @throws JsonException
     * @throws DateTimeException
     * @throws InvalidArgumentException
     */
    public static function load(string $baseDir): WebpageSnapshot
    {
        $paths = self::getPaths($baseDir);

        $data = Json::decode(file_get_contents($paths[self::PATH_META_FILE]));
        $data['contents'] = file_get_contents($paths[self::PATH_DATA_FILE]);

        $result = WebpageSnapshot::fromArray($data);

        for ($index = 0; $index < $data['childCount']; ++$index) {
            $result->addChild(self::load(self::getChildDirPath($baseDir, $index)));
        }

        return $result;
    }

    /**
     * @throws JsonException
     */
    public static function dump(string $baseDir, WebpageSnapshot $snapshot): void
    {
        self::$fs ??= new Filesystem();
        self::$fs->mkdir($baseDir);

        $paths = self::getPaths($baseDir);
        self::$fs->dumpFile($paths[self::PATH_META_FILE], Json::encode($snapshot->getMetadata(), self::JSON_SERIALIZATION_OPTIONS));
        self::$fs->dumpFile($paths[self::PATH_DATA_FILE], $snapshot->getContents());

        foreach ($snapshot->getChildren() as $index => $child) {
            self::dump(self::getChildDirPath($baseDir, $index), $child);
        }
    }

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

    private function __construct()
    {
        /* You're an utility, Harry */
    }
}
