<?php

declare(strict_types=1);

namespace App\Data\Tidying;

use App\Utils\StrUtils;
use Symfony\Component\Console\Style\SymfonyStyle;

class Printer
{
    private ?CreatorChanges $lastContext = null;
    private ?CreatorChanges $currentContext = null;

    public function __construct(
        private readonly SymfonyStyle $io,
    ) {
        Formatter::setup($io->getFormatter());
    }

    public function setCurrentContext(CreatorChanges $creator): void
    {
        $this->currentContext = $creator;
    }

    /**
     * @param string|iterable<string> $messages
     */
    public function writeln(string|iterable $messages): void
    {
        $this->showCreatorNameIfContextChanged();
        $this->io->writeln($messages);
    }

    public function note(string $message): void
    {
        $this->showCreatorNameIfContextChanged();
        $this->io->note($message);
    }

    public function warning(string $message): void
    {
        $this->showCreatorNameIfContextChanged();
        $this->io->warning($message);
    }

    public function success(string $message): void
    {
        $this->showCreatorNameIfContextChanged();
        $this->io->success($message);
    }

    private function showCreatorNameIfContextChanged(): void
    {
        if ($this->lastContext !== $this->currentContext && null !== $this->currentContext) {
            $this->io->section(StrUtils::creatorNamesSafeForCli(
                $this->currentContext->getSubject(),
                $this->currentContext->getChanged()
            ));
        }

        $this->lastContext = $this->currentContext;
    }
}
