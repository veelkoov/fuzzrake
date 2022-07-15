<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\IuSubmissions\NotificationsGenerator as Generator;
use App\Utils\Json;
use App\Utils\Notifications\SnsService;
use Exception;
use JsonException;
use Psr\Log\LoggerInterface;

class IuSubmissionService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LocalStorageService $local,
        private readonly S3StorageService $s3,
        private readonly SnsService $sns,
    ) {
    }

    public function submit(Artisan $submission): bool
    {
        try {
            $relativeFilePath = $this->local->saveOnDiskGetRelativePath($this->submissionToJson($submission));

            if ($s3SendingOk = $this->s3->sendCopyToS3($relativeFilePath)) {
                /* If successfully pushed data to S3, remove local copy. It's safer in S3 */
                $this->local->removeLocalCopy($relativeFilePath);
            }

            $this->sns->send(Generator::getMessage($submission, $s3SendingOk)); // Ignoring result. Artisans instructed to reach out to the maintainer if no change happens within X days.

            return true;
        } catch (Exception $exception) {
            $this->logger->error('Failed to submit IU form data', ['exception' => $exception]);

            return false;
        }
    }

    /**
     * @throws JsonException
     */
    private function submissionToJson(Artisan $submission): string
    {
        return Json::encode(SchemaFixer::appendSchemaVersion($submission->getAllData()), JSON_PRETTY_PRINT);
    }
}
