<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\Entity\Artisan;
use App\Utils\Json;
use Exception;
use JsonException;
use Psr\Log\LoggerInterface;

class IuSubmissionService
{
    private LoggerInterface $logger;
    private LocalStorageService $local;
    private S3StorageService $s3;
    private SnsService $sns;

    public function __construct(LoggerInterface $logger, LocalStorageService $local, S3StorageService $s3, SnsService $sns)
    {
        $this->logger = $logger;
        $this->local = $local;
        $this->s3 = $s3;
        $this->sns = $sns;
    }

    public function submit(Artisan $data): bool
    {
        try {
            $relativeFilePath = $this->local->saveOnDiskGetRelativePath($this->formatDataIntoJson($data));

            if (($s3SendingOk = $this->s3->sendCopyToS3($relativeFilePath))) {
                /* If successfully pushed data to S3, remove local copy. It's safer in S3 */
                $this->local->removeLocalCopy($relativeFilePath);
            }

            $this->sns->notifyAboutSubmission($data, $s3SendingOk); // Ignoring result. Artisans instructed to reach out to the maintainer if no change happens within X days.

            return true;
        } catch (Exception $exception) {
            $this->logger->error('Failed to submit IU form data', ['exception' => $exception]);

            return false;
        }
    }

    /**
     * @throws JsonException
     */
    private function formatDataIntoJson(Artisan $data): string
    {
        return Json::encode($data->getAllData(), JSON_PRETTY_PRINT);
    }
}
