<?php

declare(strict_types=1);

namespace App\IuHandling\Submission;

use App\IuHandling\SchemaFixer;
use App\IuHandling\Storage\LocalStorageService;
use App\IuHandling\Storage\S3StorageService;
use App\IuHandling\Submission\NotificationsGenerator as Generator;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Json;
use App\Utils\Notifications\MessengerInterface;
use Exception;
use JsonException;
use Psr\Log\LoggerInterface;

class SubmissionService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LocalStorageService $local,
        private readonly S3StorageService $s3,
        private readonly MessengerInterface $messenger,
    ) {
    }

    public function submit(Artisan $submission): bool
    {
        try {
            $relativeFilePath = $this->local->saveOnDiskGetRelativePath(self::asJson($submission));

            if ($s3SendingOk = $this->s3->sendCopyToS3($relativeFilePath)) {
                /* If successfully pushed data to S3, remove local copy. It's safer in S3 */
                $this->local->removeLocalCopy($relativeFilePath);
            }

            $this->messenger->send(Generator::getMessage($submission, $s3SendingOk)); // Ignoring result. Artisans instructed to reach out to the maintainer if no change happens within X days.

            return true;
        } catch (Exception $exception) {
            $this->logger->error('Failed to submit IU form data', ['exception' => $exception]);

            return false;
        }
    }

    /**
     * @throws JsonException
     */
    public static function asJson(Artisan $submission): string
    {
        return Json::encode(self::asArray($submission), JSON_PRETTY_PRINT);
    }

    /**
     * @return array<string, psJsonFieldValue>
     */
    public static function asArray(Artisan $submission): array
    {
        return SchemaFixer::appendSchemaVersion($submission->getAllData());
    }
}
