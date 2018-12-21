<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;
use App\Utils\ArtisanMetadata;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataDiffer
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;

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

    private function showSingleFieldDiff(bool &$nameShown, string $fieldName, Artisan $old, Artisan $new): void
    {
        $newVal = $new->get($fieldName);
        $oldVal = $old->get($fieldName);

        if ($oldVal !== $newVal) {
            $this->showNameFirstTime($nameShown, $old, $new);

            if ($oldVal) {
                $this->io->writeln("$fieldName: <d>{$oldVal}</>");
            }
            if ($newVal) {
                $this->io->writeln("$fieldName: <a>{$newVal}</>");
            }
            $this->io->writeln('');
        }
    }

    private function showNameFirstTime(bool &$nameShown, Artisan $old, Artisan $new): void
    {
        if (!$nameShown) {
            $names = array_unique(array_filter([
                $old->getName(),
                $old->getFormerly(),
                $new->getName(),
                $new->getFormerly(),
            ]));

            $this->io->section(implode('/', $names));

            $nameShown = true;
        }
    }
}
