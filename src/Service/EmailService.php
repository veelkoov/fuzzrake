<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\Email as EmailUtils;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
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
    public function send(
        string $subject,
        string $contents,
        string $recipient = '',
        string $attachedJsonData = '',
    ): void {
        $email = new Email()
            ->subject($subject)
            ->text($contents)
        ;

        if (EmailUtils::isValid($recipient)) {
            $email->to($recipient)->bcc($this->contactEmail);
        } else {
            $email->to($this->contactEmail);
        }

        if ('' !== $attachedJsonData) {
            $email->attach($attachedJsonData, 'data.json', 'application/json');
        }

        $this->sendRaw($email);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendRaw(Email $email): void
    {
        $email->from($this->contactEmail);

        $this->mailer->send($email);
    }
}
