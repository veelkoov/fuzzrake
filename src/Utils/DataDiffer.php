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
    }

    public function showDiff(Artisan $old, Artisan $new): void
    {
        $nameShown = false;

        foreach (ArtisanMetadata::getModelFieldNames() as $fieldName) {
            $this->showSingleFieldDiff($nameShown, $fieldName, $old, $new);
        }
    }

    private function showSingleFieldDiff(bool &$nameShown, string $modelFieldName, Artisan $old, Artisan $new): void
    {
        $newVal = $new->get($modelFieldName) ?: '';
        $oldVal = $old->get($modelFieldName) ?: '';
        $prettyFieldName = ArtisanMetadata::getPrettyByModelFieldName($modelFieldName);

        if ($oldVal !== $newVal) {
            $this->showNameFirstTime($nameShown, $old, $new);

            if (ArtisanMetadata::isListField($prettyFieldName)) {
                $this->showListDiff($prettyFieldName, $oldVal, $newVal);
            } else {
                $this->showSingleValueDiff($prettyFieldName, $oldVal, $newVal);
            }

            $this->showFixCommandOptionally($new->getMakerId(), $prettyFieldName, $newVal);

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

    private function showListDiff(string $fieldName, $oldVal, $newVal): void
    {
        $oldValItems = explode("\n", $oldVal);
        $newValItems = explode("\n", $newVal);
        $allItems = array_unique(array_filter(array_merge($oldValItems, $newValItems)));
        sort($allItems);

        foreach ($allItems as &$item) {
            if (in_array($item, $oldValItems)) {
                if (!in_array($item, $newValItems)) {
                    $item = "<d>$item</>";
                }
            } else {
                $item = "<a>$item</>";
            }

            $item = Utils::safeStr($item);
        }

        if ($oldVal) { // In case order changed or duplicates got removed, etc.
            $this->io->writeln("$fieldName: ".str_replace("\n", '|', $oldVal));
        }
        $this->io->writeln("$fieldName: ".join('|', $allItems));
    }

    private function showSingleValueDiff(string $fieldName, $oldVal, $newVal): void
    {
        if ($oldVal) {
            $oldVal = Utils::safeStr($oldVal);
            $this->io->writeln("$fieldName: <d>$oldVal</>");
        }

        if ($newVal) {
            $newVal = Utils::safeStr($newVal);
            $this->io->writeln("$fieldName: <a>$newVal</>");
        }
    }

    private function showFixCommandOptionally(string $makerId, string $prettyFieldName, string $value)
    {
        if ($this->showFixCommands) {
            $value = Utils::safeStr($value);
            $this->io->writeln("wr:$makerId:$prettyFieldName:|:$value|ABCDEFGHIJ|");
        }
    }
}
