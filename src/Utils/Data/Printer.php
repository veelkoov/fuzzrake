<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Utils\StrUtils;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class Printer
{
    private ?ArtisanFixWip $lastContext = null;
    private ?ArtisanFixWip $currentContext = null;

    public function __construct(
        private SymfonyStyle $io,
    ) {
        $this->io->getFormatter()->setStyle('diff_added', new OutputFormatterStyle('green'));
        $this->io->getFormatter()->setStyle('diff_deleted', new OutputFormatterStyle('red'));
        $this->io->getFormatter()->setStyle('diff_imported', new OutputFormatterStyle('magenta'));

        $this->io->getFormatter()->setStyle('invalid', new OutputFormatterStyle('red'));
        $this->io->getFormatter()->setStyle('fix', new OutputFormatterStyle('blue'));
    }

    public static function formatImported(string $item): string
    {
        return "<diff_imported>$item</>";
    }

    public static function formatDeleted(string $item): string
    {
        return "<diff_deleted>$item</>";
    }

    public static function formatAdded(string $item): string
    {
        return "<diff_added>$item</>";
    }

    public static function formatInvalid(string $item): string
    {
        return "<invalid>$item</>";
    }

    public static function formatFix(string $item): string
    {
        return "<fix>$item</>";
    }

    public function setCurrentContext(ArtisanFixWip $artisan)
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
                $this->currentContext->getOriginal(),
                $this->currentContext->getFixed()
            ));
        }

        $this->lastContext = $this->currentContext;
    }
}
