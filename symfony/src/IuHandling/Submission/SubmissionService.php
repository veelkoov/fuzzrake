<?php

declare(strict_types=1);

namespace App\IuHandling\Submission;

use App\Entity\Submission;
use App\IuHandling\Exception\SubmissionException;
use App\IuHandling\SchemaFixer;
use App\IuHandling\Submission\NotificationsGenerator as Generator;
use App\Repository\SubmissionRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Json;
use JsonException;
use Psr\Log\LoggerInterface;
use Random\RandomException;
use Symfony\Component\Messenger\Exception\ExceptionInterface as MessengerException;
use Symfony\Component\Messenger\MessageBusInterface;

class SubmissionService
{
    public function __construct(
        private readonly SubmissionRepository $submissionRepository,
        private readonly LoggerInterface $logger,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws SubmissionException
     */
    public function submit(Creator $submissionData): Submission
    {
        try {
            $submission = self::getEntityForSubmission($submissionData);

            $this->submissionRepository->add($submission, true);
            $this->sendNotification($submissionData, $submission->getPayload());

            return $submission;
        } catch (JsonException|RandomException $exception) {
            throw new SubmissionException(previous: $exception);
        }
    }

    /**
     * @throws JsonException|RandomException
     */
    public static function getEntityForSubmission(Creator $submissionData): Submission
    {
        $result = new Submission();
        $result->setPayload(self::asJson($submissionData));

        return $result;
    }

    /**
     * @throws JsonException
     */
    public static function asJson(Creator $submission): string // TODO: Double check if needs to be public
    {
        return Json::encode(SchemaFixer::appendSchemaVersion($submission->getAllData()), JSON_PRETTY_PRINT);
    }

    private function sendNotification(Creator $submission, string $jsonData): void
    {
        try {
            $this->messageBus->dispatch(Generator::getMessage($submission, $jsonData));
        } catch (MessengerException $exception) {
            $this->logger->error('Failed sending notification.', ['exception' => $exception]);
        }
    }
}
