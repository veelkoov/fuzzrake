<?php

namespace App\Service\Notifications;

use App\Utils\Email as EmailUtils;
use App\ValueObject\Notification;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService implements MessengerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $contactEmail,
        private readonly MailerInterface $mailer,
    ) {
    }

    #[Override]
    public function send(Notification $notification): bool
    {
        $email = (new Email())
            ->from($this->contactEmail)
            ->subject($notification->subject)
            ->text($notification->contents)
        ;

        if (EmailUtils::isValid($notification->recipient)) {
            $email->to($notification->recipient)->cc($this->contactEmail);
        } else {
            $email->to($this->contactEmail);
        }

        if ('' !== $notification->attachedJsonData) {
            $email->attach($notification->attachedJsonData, 'data.json', 'application/json');
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface) {
            try { // Retry once. TODO: https://github.com/veelkoov/fuzzrake/issues/258
                $this->mailer->send($email);
            } catch (TransportExceptionInterface $exception) {
                $this->logger->warning('Sending email failed.', ['exception' => $exception->getMessage()]);

                return false;
            }
        }

        return true;
    }
}
