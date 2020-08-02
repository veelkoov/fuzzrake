<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;
use App\Utils\Artisan\Fields;
use App\Utils\Json;
use App\Utils\StrUtils;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class IuFormService
{
    private LoggerInterface $logger;
    private string $s3CopiesBucketUrl;
    private string $notificationsSnsTopicArn;
    private IuFormSubmissionLocalService $local;

    public function __construct(LoggerInterface $logger, IuFormSubmissionLocalService $iuFormSubmissionLocalService, string $iuFormDataS3CopiesBucketUrl, string $notificationSnsTopicArn)
    {
        $this->logger = $logger;
        $this->local = $iuFormSubmissionLocalService;
        $this->s3CopiesBucketUrl = $iuFormDataS3CopiesBucketUrl;
        $this->notificationsSnsTopicArn = $notificationSnsTopicArn;

        if (pattern('^s3://[-.a-z0-9]+$', 'i')->fails($iuFormDataS3CopiesBucketUrl)) {
            throw new InvalidArgumentException("$iuFormDataS3CopiesBucketUrl is not a valid S3 bucket URL");
        }

        if (pattern('^arn:aws:sns:[-a-z0-9]+:\d+:[-_a-z0-9]+$', 'i')->fails($notificationSnsTopicArn)) {
            throw new InvalidArgumentException("$notificationSnsTopicArn is not a valid SNS topic ARN");
        }
    }

    public function submit(Artisan $data): bool
    {
        try {
            $jsonData = Json::encode($data->getAllData(), JSON_PRETTY_PRINT);

            $relativeFilePath = $this->local->saveOnDiskGetRelativePath($jsonData);
            $this->sendCopyToS3($relativeFilePath);
            $this->notifyAboutSubmission($data);

            return true;
        } catch (Exception $exception) {
            $this->logger->error('Failed to submit IU form data', ['exception' => $exception]);

            return false;
        }
    }

    private function notifyAboutSubmission(Artisan $data): void
    {
        $names = StrUtils::artisanNamesSafeForCli($data);
        $message = <<<MESSAGE
            {$names}
            From: {$data->getCountry()}
            
            MESSAGE;

        foreach (Fields::urls() as $url) {
            if (($val = $data->get($url))) {
                $message .= $url->name().': '.$val."\n";
            }
        }

        $this->runAwsCliCmd(['aws', 'sns', 'publish', '--topic-arn', $this->notificationsSnsTopicArn,
            '--subject', "IU submission: {$data->getName()}", '--message', $message, ], 'Sending copy to S3');
    }

    /**
     * The only purpose of this is to have a backup of the submission in case something bad happened to the VM.
     */
    private function sendCopyToS3(string $relativeFilePath): void
    {
        $this->runAwsCliCmd(['aws', 's3', 'cp', $this->local->getAbsolutePath($relativeFilePath),
            $this->s3CopiesBucketUrl.'/'.$relativeFilePath, ], 'Sending copy to S3');
    }

    private function runAwsCliCmd(array $command, string $description): void
    {
        $process = new Process($command);
        $process->mustRun();

        $context = [
            'stdout' => $process->getOutput(),
            'stderr' => $process->getErrorOutput(),
        ];

        if ($process->isSuccessful()) {
            $this->logger->info("$description successful", $context);
        } else {
            // TODO: Consider throwing up
            $this->logger->error("$description failed", $context);
        }
    }
}
