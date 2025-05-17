<?php

declare(strict_types=1);

namespace App\Command;

use App\ValueObject\Messages\SpeciesSyncNotificationV1;
use App\ValueObject\Messages\UpdateMiniaturesV1;
use LogicException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Veelkoov\Debris\StringList;

#[AsCommand(
    name: 'app:message',
    description: 'Send a chosen message',
)]
final class MessageCommand extends Command
{
    private const string MSG_SPECIES = 'SPECIES';
    private const string MSG_MINIATURES = 'MINIATURES';

    private readonly StringList $messageOptions;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
        $this->messageOptions = StringList::of(self::MSG_SPECIES, self::MSG_MINIATURES);

        parent::__construct();
    }

    protected function configure(): void
    {
        $messageDescription = "Choices: {$this->messageOptions->join(', ')}";

        $this
            ->addArgument('MESSAGE', InputArgument::REQUIRED, $messageDescription);
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $message = $input->getArgument('MESSAGE');

        if (!$this->messageOptions->contains($message)) {
            $io->error('Wrong message specified.');

            return Command::FAILURE;
        }

        $messageObject = match ($message) {
            self::MSG_SPECIES => new SpeciesSyncNotificationV1(),
            self::MSG_MINIATURES => new UpdateMiniaturesV1(),
            default => throw new LogicException('Not implemented.'),
        };

        $this->messageBus->dispatch($messageObject);
        $io->success('Message sent successfully.');

        return Command::SUCCESS;
    }
}
