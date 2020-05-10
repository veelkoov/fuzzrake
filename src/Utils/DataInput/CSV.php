<?php

declare(strict_types=1);

namespace App\Utils\DataInput;

use InvalidArgumentException;
use Symfony\Component\Mime\MimeTypes;
use ZipArchive;

abstract class CSV
{
    public static function arrayFromFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("File '$filePath' does not exist");
        }

        if ('application/zip' === MimeTypes::getDefault()->guessMimeType($filePath)) {
            $fileContents = self::readOnlyFileInZip($filePath);
        } else {
            $fileContents = file_get_contents($filePath);
        }

        return self::arrayFromCsvString($fileContents);
    }

    private static function arrayFromCsvString(string $csvString): array
    {
        $fileHandle = self::getTmpFileWithContents($csvString);

        $result = self::arrayFromCsvFileHandle($fileHandle);
        array_shift($result); // Drop header

        fclose($fileHandle);

        return $result;
    }

    private static function arrayFromCsvFileHandle($fileHandle): array
    {
        $result = [];
        while (false !== ($cols = fgetcsv($fileHandle, 0, ',', '"', '"'))) {
            $result[] = $cols;
        }

        return $result;
    }

    private static function readOnlyFileInZip(string $filePath): string
    {
        $zip = new ZipArchive();
        if (true !== $zip->open($filePath)) {
            throw new InvalidArgumentException("File '$filePath' is a corrupted ZIP archive");
        }

        if (1 !== $zip->numFiles) {
            $zip->close();
            throw new InvalidArgumentException("Unexpected number of files in the '$filePath' ZIP archive: {$zip->numFiles}");
        }

        $result = $zip->getFromIndex(0);
        $zip->close();

        if (false === $result) {
            throw new InvalidArgumentException("Failed reading file from the '$filePath' ZIP archive");
        }

        return $result;
    }

    private static function getTmpFileWithContents(string $contents)
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $contents);
        rewind($handle);

        return $handle;
    }
}
