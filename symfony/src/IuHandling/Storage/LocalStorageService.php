<?php

declare(strict_types=1);

namespace App\IuHandling\Storage;

use App\Utils\DateTime\UtcClock;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

class LocalStorageService
{
    private readonly Filesystem $filesystem;
    private readonly string $dataDirPath;

    public function __construct(
        #[Autowire('%env(resolve:SUBMISSIONS_DIR_PATH)%')]
        string $dataDirPath,
    ) {
        $this->dataDirPath = $dataDirPath;
        $this->filesystem = new Filesystem();
    }

    /**
     * @throws Exception
     */
    public function saveOnDiskGetRelativePath(string $jsonData): string
    {
        do {
            $relativeFilePath = UtcClock::now()
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
}
