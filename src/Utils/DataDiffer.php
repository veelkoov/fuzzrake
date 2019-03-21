<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataDiffer
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var bool
     */
    private $showFixCommands;

    public function __construct(SymfonyStyle $io, bool $showFixCommands = false)
    {
        $this->io = $io;
        $this->showFixCommands = $showFixCommands;

        $this->io->getFormatter()->setStyle('a', new OutputFormatterStyle('green'));
        $this->io->getFormatter()->setStyle('d', new OutputFormatterStyle('red'));
        $this->io->getFormatter()->setStyle('i', new OutputFormatterStyle('black', 'cyan'));
    }

    public function showDiff(Artisan $old, Artisan $new, Artisan $imported = null): void
    {
        $nameShown = false;

        foreach (ArtisanMetadata::getModelFieldNames() as $fieldName) {
            $this->showSingleFieldDiff($nameShown, $fieldName, $old, $new, $imported);
        }
    }

    private function showSingleFieldDiff(bool &$nameShown, string $modelFieldName, Artisan $old, Artisan $new, ?Artisan $imported): void
    {
        $newVal = $new->get($modelFieldName) ?: '';
        $oldVal = $old->get($modelFieldName) ?: '';
        $impVal = $imported ? $imported->get($modelFieldName) : null;
        $prettyFieldName = ArtisanMetadata::getPrettyByModelFieldName($modelFieldName);

        if ($oldVal !== $newVal) {
            $this->showNameFirstTime($nameShown, $old, $new);

            if (ArtisanMetadata::isListField($prettyFieldName)) {
                $this->showListDiff($prettyFieldName, $oldVal, $newVal, $impVal);
            } else {
                $this->showSingleValueDiff($prettyFieldName, $oldVal, $newVal, $impVal);
            }

            $this->showFixCommandOptionally($new->getMakerId(), $prettyFieldName, $impVal ?? $oldVal, $newVal);

            $this->io->writeln('');
        }
    }

    private function showNameFirstTime(bool &$nameShown, Artisan $old, Artisan $new): void
    {
        if (!$nameShown) {
            $this->io->section(Utils::artisanNames($old, $new));

            $nameShown = true;
        }
    }

    private function showListDiff(string $fieldName, $oldVal, $newVal, $impVal = null): void
    {
        $oldValItems = explode("\n", $oldVal);
        $newValItems = explode("\n", $newVal);

        foreach ($oldValItems as &$item) {
            if (!in_array($item, $newValItems)) {
                $item = "<d>$item</>";
            }

            $item = Utils::safeStr($item);
        }

        foreach ($newValItems as &$item) {
            if (!in_array($item, $oldValItems)) {
                $item = "<a>$item</>";
            }

            $item = Utils::safeStr($item);
        }

        if ($impVal && $impVal !== $newVal) {
            $impVal = Utils::safeStr($impVal ?: '');
            $this->io->writeln("IMP $fieldName: <i>$impVal</>");
        }

        if ($oldVal) { // In case order changed or duplicates got removed, etc.
            $this->io->writeln("OLD $fieldName: ".implode('|', $oldValItems));
        }

        $this->io->writeln("NEW $fieldName: ".implode('|', $newValItems));
    }

    private function showSingleValueDiff(string $fieldName, $oldVal, $newVal, $impVal = null): void
    {
        if ($impVal && $impVal !== $newVal) {
            $impVal = Utils::safeStr($impVal ?: '');
            $this->io->writeln("IMP $fieldName: <i>$impVal</>");
        }

        if ($oldVal) {
            $oldVal = Utils::safeStr($oldVal);
            $this->io->writeln("OLD $fieldName: <d>$oldVal</>");
        }

        if ($newVal) {
            $newVal = Utils::safeStr($newVal);
            $this->io->writeln("NEW $fieldName: <a>$newVal</>");
        }
    }

    private function showFixCommandOptionally(string $makerId, string $prettyFieldName, string $replaced, string $best)
    {
        if ($this->showFixCommands) {
            $replaced = Utils::safeStr($replaced);
            $best = Utils::safeStr($best);
            $this->io->writeln("wr:$makerId:$prettyFieldName:|:$replaced|$best|");
        }
    }
}
