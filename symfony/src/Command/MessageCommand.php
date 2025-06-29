<?php

declare(strict_types=1);

namespace App\Command;

use App\ValueObject\Messages\InitiateTrackingV1;
use App\ValueObject\Messages\SpeciesSyncNotificationV1;
use App\ValueObject\Messages\UpdateMiniaturesV1;
use LogicException;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Veelkoov\Debris\StringList;

#[AsCommand(
    name: 'app:message',
    description: 'Send a chosen message',
)]
final class MessageCommand
{
    private const string MSG_SPECIES = 'SPECIES';
    private const string MSG_MINIATURES = 'MINIATURES';
    private const string MSG_TRACKING = 'TRACKING';

    private readonly StringList $messageOptions;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
        $this->messageOptions = StringList::of(self::MSG_SPECIES, self::MSG_MINIATURES, self::MSG_TRACKING);
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(
        SymfonyStyle $io,
        #[Argument(description: 'Choices: SPECIES, MINIATURES, TRACKING')] string $message,
    ): int {
        if (!$this->messageOptions->contains($message)) {
            $io->error('Wrong message specified.');

            return Command::FAILURE;
        }

        $messageObject = match ($message) {
            self::MSG_SPECIES => new SpeciesSyncNotificationV1(),
            self::MSG_MINIATURES => new UpdateMiniaturesV1(),
            self::MSG_TRACKING => new InitiateTrackingV1(), // TODO: No retries? No refetch?
            default => throw new LogicException('Not implemented.'),
        };

        $this->messageBus->dispatch($messageObject);
        $io->success('Message sent successfully.');

        return Command::SUCCESS;
    }
}
