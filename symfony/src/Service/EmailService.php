<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\Email as EmailUtils;
use App\ValueObject\Messages\EmailNotificationV1;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

final class EmailService
{
    public function __construct(
        #[Autowire(env: 'CONTACT_EMAIL')]
        private readonly string $contactEmail,
        private readonly MailerInterface $mailer,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[AsMessageHandler]
    public function send(EmailNotificationV1 $notification): void
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

        $this->mailer->send($email);
    }
}
