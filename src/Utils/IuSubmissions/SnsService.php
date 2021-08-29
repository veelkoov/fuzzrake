<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\DataDefinitions\Fields;
use App\Service\AwsCliService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StrUtils;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class SnsService
{
    public function __construct(
        private LoggerInterface $logger,
        private AwsCliService $cli,
        private string $notificationSnsTopicArn,
    ) {
        if (pattern('^(arn:aws:sns:[-a-z0-9]+:\d+:[-_a-z0-9]+)?$', 'i')->fails($notificationSnsTopicArn)) {
            throw new InvalidArgumentException("$notificationSnsTopicArn is not a valid SNS topic ARN");
        }
    }

    public function notifyAboutSubmission(Artisan $data, bool $s3SendingOk): bool
    {
        if ('' === $this->notificationSnsTopicArn) {
            $this->logger->warning('Unable to send SNS notification - the URL is not configured');

            return false;
        }

        $optionalWarning = $s3SendingOk ? '' : "WARNING: S3 sending failed!\n\n";

        $names = StrUtils::artisanNamesSafeForCli($data);
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection Readability */
        $message = <<<MESSAGE
            {$optionalWarning}{$names}
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
