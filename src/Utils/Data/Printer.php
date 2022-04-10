<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Utils\Console\Formatter;
use App\Utils\StrUtils;
use Symfony\Component\Console\Style\SymfonyStyle;

class Printer
{
    private ?ArtisanChanges $lastContext = null;
    private ?ArtisanChanges $currentContext = null;

    public function __construct(
        private readonly SymfonyStyle $io,
    ) {
        Formatter::setup($io->getFormatter());
    }

    public function setCurrentContext(ArtisanChanges $artisan): void
    {
        $this->currentContext = $artisan;
    }

    public function writeln($messages): void
    {
        $this->showArtisanNameIfContextChanged();
        $this->io->writeln($messages);
    }

    public function note(string $message): void
    {
        $this->showArtisanNameIfContextChanged();
        $this->io->note($message);
    }

    public function warning(string $message): void
    {
        $this->showArtisanNameIfContextChanged();
        $this->io->warning($message);
    }

    public function success(string $message): void
    {
        $this->showArtisanNameIfContextChanged();
        $this->io->success($message);
    }

    private function showArtisanNameIfContextChanged(): void
    {
        if ($this->lastContext !== $this->currentContext) {
            $this->io->section(StrUtils::artisanNamesSafeForCli(
                $this->currentContext->getSubject(),
                $this->currentContext->getChanged()
            ));
        }

        $this->lastContext = $this->currentContext;
    }
}
