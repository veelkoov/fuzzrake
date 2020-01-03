<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataDiffer
{
    private SymfonyStyle $io;
    private bool $showFixCommands;

    public function __construct(SymfonyStyle $io, bool $showFixCommands = false)
    {
        $this->io = $io;
        $this->showFixCommands = $showFixCommands;

        $this->io->getFormatter()->setStyle('a', new OutputFormatterStyle('green'));
        $this->io->getFormatter()->setStyle('d', new OutputFormatterStyle('red'));
        $this->io->getFormatter()->setStyle('i', new OutputFormatterStyle('black', 'cyan'));
        $this->io->getFormatter()->setStyle('f', new OutputFormatterStyle('blue'));
    }

    public function showDiff(Artisan $old, Artisan $new, Artisan $imported = null): void
    {
        $nameShown = false;

        foreach (Fields::persisted() as $field) {
            $this->showSingleFieldDiff($nameShown, $field, $old, $new, $imported);
        }
    }

    private function showSingleFieldDiff(bool &$nameShown, Field $field, Artisan $old, Artisan $new, ?Artisan $imported): void
    {
        $newVal = $new->get($field) ?: '';
        $oldVal = $old->get($field) ?: '';
        $impVal = $imported ? $imported->get($field) : null;

        if ($oldVal !== $newVal) {
            $this->showArtisanNameIfFirstTime($nameShown, $old, $new);

            if ($field->isList()) {
                $this->showListDiff($field->name(), $oldVal, $newVal, $impVal);
            } else {
                $this->showSingleValueDiff($field->name(), $oldVal, $newVal, $impVal);
            }

            $this->showFixCommandOptionally($new->getMakerId(), $field, $impVal ?? $oldVal, $newVal);

            $this->io->writeln('');
        }
    }

    private function showArtisanNameIfFirstTime(bool &$nameShown, Artisan $old, Artisan $new): void
    {
        if (!$nameShown) {
            $this->io->section(StrUtils::artisanNamesSafeForCli($old, $new));

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

            $item = StrUtils::strSafeForCli($item);
        }

        foreach ($newValItems as &$item) {
            if (!in_array($item, $oldValItems)) {
                $item = "<a>$item</>";
            }

            $item = StrUtils::strSafeForCli($item);
        }

        if ($impVal && $impVal !== $newVal) {
            $impVal = StrUtils::strSafeForCli($impVal ?: '');
            $this->io->writeln("IMP $fieldName: <i>$impVal</>");
        }

        if ($oldVal) { // In case order changed or duplicates got removed, etc.
            $this->io->writeln("OLD $fieldName: ".implode('|', $oldValItems));
        }

        $this->io->writeln("NEW $fieldName: ".implode('|', $newValItems));
    }

    private function showSingleValueDiff(string $fieldName, $oldVal, $newVal, $impVal = null): void
    {
        if ($impVal && $impVal !== $newVal && !$this->skipImpValue($fieldName)) {
            $impVal = StrUtils::strSafeForCli($impVal ?: '');
            $this->io->writeln("IMP $fieldName: <i>$impVal</>");
        }

        if ($oldVal) {
            $oldVal = StrUtils::strSafeForCli($oldVal);
            $this->io->writeln("OLD $fieldName: <d>$oldVal</>");
        }

        if ($newVal) {
            $newVal = StrUtils::strSafeForCli($newVal);
            $this->io->writeln("NEW $fieldName: <a>$newVal</>");
        }
    }

    private function showFixCommandOptionally(string $makerId, Field $field, string $replaced, string $best)
    {
        if ($this->showFixCommands && !$this->skipFixCommand($field->name())) {
            $replaced = StrUtils::strSafeForCli($replaced);
            $best = StrUtils::strSafeForCli($best);
            $this->io->writeln("<f>wr:$makerId:{$field->name()}:|:$replaced|$best|</f>");
        }
    }

    private function skipImpValue(string $fieldName): bool
    {
        return in_array($fieldName, [Fields::CONTACT_ALLOWED, Fields::CONTACT_METHOD, Fields::CONTACT_INFO_OBFUSCATED]);
    }

    private function skipFixCommand(string $fieldName): bool
    {
        return in_array($fieldName, [
            Fields::CONTACT_ALLOWED,
            Fields::CONTACT_METHOD,
            Fields::CONTACT_INFO_OBFUSCATED,
            Fields::CONTACT_ADDRESS_PLAIN,
        ]);
    }
}
