<?php

declare(strict_types=1);

namespace App\Service\Notifications;

use App\Service\AwsCliService;
use App\Service\EnvironmentsService;
use App\ValueObject\Notification;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SnsService implements MessengerInterface
{
    private readonly string $notificationSnsTopicArn;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly AwsCliService $cli,
        #[Autowire('%env(resolve:NOTIFICATIONS_TOPIC_ARN)%')]
        string $notificationSnsTopicArn,
        private readonly EnvironmentsService $environments,
    ) {
        if (pattern('^(arn:aws:sns:[-a-z0-9]+:\d+:[-_a-z0-9]+)?$', 'i')->fails($notificationSnsTopicArn)) {
            throw new InvalidArgumentException("$notificationSnsTopicArn is not a valid SNS topic ARN");
        }

        $this->notificationSnsTopicArn = $notificationSnsTopicArn;
    }

    public function send(Notification $notification): bool
    {
        if ('' === $this->notificationSnsTopicArn) {
            if ($this->environments->isTest()) {
                $this->logger->info('SNS ARN not configured in test environment - skipping sending the notification.');

                return true;
            } else { // @codeCoverageIgnoreStart
                $this->logger->warning('Unable to send SNS notification - the ARN is not configured.', ['notification' => $notification]);

                return false;
            } // @codeCoverageIgnoreEnd
        }

        return $this->cli->execute(['aws', 'sns', 'publish', '--topic-arn', $this->notificationSnsTopicArn,
            '--subject', $notification->subject, '--message', $notification->contents, ], "Sending SNS notification: '$notification->subject'");
    }
}
