<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;
use App\Utils\DateTime\DateTimeUtils;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class IuFormService
{
    private LoggerInterface $logger;
    private string $dataDirPath;
    private Filesystem $filesystem;
    private string $s3CopiesBucketUrl;

    public function __construct(LoggerInterface $logger, string $iuFormDataDirPath, string $iuFormDataS3CopiesBucketUrl)
    {
        $this->filesystem = new Filesystem();

        $this->logger = $logger;
        $this->dataDirPath = $iuFormDataDirPath;
        $this->s3CopiesBucketUrl = $iuFormDataS3CopiesBucketUrl;

        if (pattern('^s3://[-.a-z0-9]+$')->fails($iuFormDataS3CopiesBucketUrl)) {
            throw new InvalidArgumentException("$iuFormDataS3CopiesBucketUrl is not a valid S3 bucket URL");
        }
    }

    public function submit(Artisan $data): bool
    {
        try {
            $jsonData = json_encode($data);

            $filePath = $this->saveOnDisk($jsonData);
            $this->sendCopyToS3($filePath);
            $this->notifyAboutSubmission($filePath, $data);

            return true;
        } catch (Exception $exception) {
            $this->logger->error('Failed to submit IU form data', ['exception' => $exception]);

            return false;
        }
    }

    /**
     * @throws Exception
     */
    private function saveOnDisk(string $jsonData): string
    {
        $this->filesystem->mkdir($this->dataDirPath);

        do {
            $filePath = $this->dataDirPath.'/'.DateTimeUtils::getNowUtc()->format('Y-m-d_H:i:s').random_int(1000, 9999);
        } while ($this->filesystem->exists($filePath)); // Accepting risk of possible overwrite

        $this->filesystem->dumpFile($filePath, $jsonData);

        return $filePath;
    }

    private function sendCopyToS3(string $filePath): void
    {
        $process = new Process(['aws', 's3', 'cp', $filePath, $this->s3CopiesBucketUrl]);
        $process->mustRun();

        $context = [
            'stdout' => $process->getOutput(),
            'stderr' => $process->getErrorOutput(),
        ];

        if ($process->isSuccessful()) {
            $this->logger->info('Sent copy to S3', $context);
        } else {
            // TODO: Consider throwing up
            $this->logger->error('Failed sending copy to S3', $context);
        }
    }

    private function notifyAboutSubmission(string $filePath, Artisan $data): void
    {
        // TODO
    }
}
