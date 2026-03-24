<?php

declare(strict_types=1);

namespace App\IuHandling;

use App\Data\Definitions\Fields\Fields;
use App\Entity\Submission;
use App\Entity\User;
use App\IuHandling\Exception\SubmissionException;
use App\Repository\SubmissionRepository;
use App\Service\EmailService;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Json;
use App\Utils\StrUtils;
use JsonException;
use Psr\Log\LoggerInterface;

class SubmissionService
{
    public function __construct(
        private readonly SubmissionRepository $submissionRepository,
        private readonly EmailService $emailService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws SubmissionException
     */
    public function submit(User $user, Creator $submissionData): Submission
    {
        $submission = $this->getEntityForSubmission($user, $submissionData);

        $this->submissionRepository->add($submission, true);
        $this->sendNotification($submissionData, $submission->getPayload());

        return $submission;
    }

    public function getEntityForSubmission(User $user, Creator $submissionData): Submission
    {
        return new Submission(null !== $submissionData->getId())
            ->setPayload($this->asJson($submissionData))
            ->setCreator($user->getCreator())
            ->setOwner($user)
        ;
    }

    private function asJson(Creator $submission): string
    {
        try {
            return Json::encode(SchemaFixer::appendSchemaVersion($submission->getAllData()));
        } catch (JsonException $exception) {
            $this->logger->error('Failed encoding submission as JSON.', ['exception' => $exception]);

            throw new SubmissionException('Failed encoding submission as JSON.', previous: $exception);
        }
    }

    private function sendNotification(Creator $submission, string $jsonData): void
    {
        $subject = "IU submission: {$submission->getName()}";
        $message = $this->getNotificationContents($submission);

        $this->emailService->send($subject, $message, attachedJsonData: $jsonData);
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
