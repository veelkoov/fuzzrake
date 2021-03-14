<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\Utils\DateTime\DateTimeUtils;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class LocalStorageService
{
    private Filesystem $filesystem;

    public function __construct(
        private LoggerInterface $logger,
        private string $dataDirPath,
    ) {
        $this->filesystem = new Filesystem();
    }

    /**
     * @throws Exception
     */
    public function saveOnDiskGetRelativePath(string $jsonData): string
    {
        do {
            $relativeFilePath = DateTimeUtils::getNowUtc()
                    ->format('Y/m/d/H:i:s').'_'.random_int(1000, 9999).'.json';
            $filePath = $this->getAbsolutePath($relativeFilePath);
        } while ($this->filesystem->exists($filePath)); // Accepting risk of possible overwrite

        $this->filesystem->mkdir(dirname($filePath));
        $this->filesystem->dumpFile($filePath, $jsonData);

        return $relativeFilePath;
    }

    public function getAbsolutePath(string $relativeFilePath): string
    {
        return $this->dataDirPath.'/'.$relativeFilePath;
    }

    public function removeLocalCopy(string $relativeFilePath): void
    {
        $this->filesystem->remove($this->getAbsolutePath($relativeFilePath));
    }
}
