<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\Email as EmailUtils;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    public function __construct(
        #[Autowire(env: 'CONTACT_EMAIL')]
        private readonly string $contactEmail,
        private readonly MailerInterface $mailer,
        private readonly BodyRendererInterface $bodyRenderer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function send(
        string $subject,
        string $contents,
        string $recipient = '',
        string $attachedJsonData = '',
        bool $bccSelf = false,
    ): void {
        $email = new Email()
            ->subject($subject)
            ->text($contents)
        ;

        if (EmailUtils::isValid($recipient)) {
            $email->to($recipient);

            if ($bccSelf) {
                $email->bcc($this->contactEmail);
            }
        } else {
            $email->to($this->contactEmail);
        }

        if ('' !== $attachedJsonData) {
            $email->attach($attachedJsonData, 'data.json', 'application/json');
        }

        $this->sendRaw($email);
    }

    public function sendRaw(Email $email): void
    {
        $email->from($this->contactEmail);

        if ($email instanceof TemplatedEmail) {
            $this->bodyRenderer->render($email);
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            // We rely on the messenger. If it's not working, everything is broken.
            // Fail the request, make user aware, don't bother with a helpful message.
            $this->logger->emergency('Failed to send email.', ['exception' => $exception]);
            throw new RuntimeException(previous: $exception);
        }
    }
}
