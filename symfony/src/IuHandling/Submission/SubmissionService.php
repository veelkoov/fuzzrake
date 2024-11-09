<?php

declare(strict_types=1);

namespace App\IuHandling\Submission;

use App\IuHandling\SchemaFixer;
use App\IuHandling\Storage\LocalStorageService;
use App\IuHandling\Submission\NotificationsGenerator as Generator;
use App\Service\Notifications\MessengerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Json;
use Exception;
use JsonException;
use Psr\Log\LoggerInterface;

class SubmissionService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LocalStorageService $local,
        private readonly MessengerInterface $messenger,
    ) {
    }

    public function submit(Artisan $submission): bool
    {
        try {
            $jsonData = self::asJson($submission);
            $this->local->saveOnDiskGetRelativePath($jsonData);

            $this->messenger->send(Generator::getMessage($submission, $jsonData)); // Ignoring result. Creators instructed to reach out to the maintainer if no change happens within X days.

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
        return Json::encode(SchemaFixer::appendSchemaVersion($submission->getAllData()), JSON_PRETTY_PRINT);
    }
}
