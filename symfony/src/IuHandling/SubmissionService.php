<?php

declare(strict_types=1);

namespace App\IuHandling;

use App\Data\Definitions\Fields\Fields;
use App\Entity\Submission;
use App\IuHandling\Exception\SubmissionException;
use App\Repository\SubmissionRepository;
use App\Service\EmailService;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Json;
use App\Utils\StrUtils;
use JsonException;
use Psr\Log\LoggerInterface;
use Random\RandomException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SubmissionService
{
    public function __construct(
        private readonly SubmissionRepository $submissionRepository,
        private readonly LoggerInterface $logger,
        private readonly EmailService $emailService,
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
    private static function asJson(Creator $submission): string
    {
        return Json::encode(SchemaFixer::appendSchemaVersion($submission->getAllData()));
    }

    private function sendNotification(Creator $submission, string $jsonData): void
    {
        $subject = "IU submission: {$submission->getName()}";
        $message = $this->getNotificationContents($submission);

        try {
            $this->emailService->send($subject, $message, attachedJsonData: $jsonData);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Failed sending notification.', ['exception' => $exception]);
        }
    }

    private function getNotificationContents(Creator $submission): string
    {
        $names = StrUtils::creatorNamesSafeForCli($submission);

        $result = <<<MESSAGE
            {$names}
            From: {$submission->getCountry()}

            MESSAGE;

        foreach (Fields::urls() as $urlField) {
            $url = $submission->get($urlField);

            if ('' !== $url) {
                $url = StrUtils::asStr($url);

                $result .= $urlField->value.': '.$url."\n";
            }
        }

        return $result;
    }
}
