<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;
use App\Utils\Artisan\Fields;
use App\Utils\StrUtils;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class IuFormSnsService
{
    private AwsCliService $cli;
    private string $notificationSnsTopicArn;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, AwsCliService $cli, string $notificationSnsTopicArn)
    {
        $this->logger = $logger;
        $this->cli = $cli;
        $this->notificationSnsTopicArn = $notificationSnsTopicArn;

        if (pattern('^arn:aws:sns:[-a-z0-9]+:\d+:[-_a-z0-9]+$', 'i')->fails($notificationSnsTopicArn)) {
            throw new InvalidArgumentException("$notificationSnsTopicArn is not a valid SNS topic ARN");
        }
    }

    public function notifyAboutSubmission(Artisan $data): bool
    {
        if ('' === $this->notificationSnsTopicArn) {
            $this->logger->warning('Unable to send SNS notification - the URL is not configured');

            return false;
        }

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

        return $this->cli->execute(['aws', 'sns', 'publish', '--topic-arn', $this->notificationSnsTopicArn,
            '--subject', "IU submission: {$data->getName()}", '--message', $message, ], 'Sending copy to S3');
    }
}
