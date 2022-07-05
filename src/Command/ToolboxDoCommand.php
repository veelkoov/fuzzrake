<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:toolbox:do')]
class ToolboxDoCommand extends Command
{
    public function __construct(
        private readonly ArtisanRepository $artisanRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->performActions($io);
        } catch (Exception $e) {
            $io->error($e->getMessage());

            return 1;
        }

        return 0;
    }

    protected function performActions(SymfonyStyle $io): void
    {
        $activeMakers = SmartAccessDecorator::wrapAll($this->artisanRepository->getActive());

        $completenessSum = 0;
        $countWwm = 0;
        $countWwms = 0;

        $sfwTrue = 0;
        $sfwNull = 0;
        $sfwFalse = 0;

        $age = 0;

        $count = 0;

        foreach ($activeMakers as $maker) {
            $completeness = $maker->getCompleteness();

            ++$count;
            $completenessSum += $completeness;

            if ($maker->getSafeWorksWithMinors()) {
                ++$countWwms;
            }

            if ($maker->getWorksWithMinors()) {
                ++$countWwm;
            }

            if (null === $maker->getNsfwWebsite() && null === $maker->getNsfwSocial()) {
                ++$sfwNull;
            } elseif (true === $maker->getNsfwWebsite() || true === $maker->getNsfwSocial()) {
                ++$sfwFalse;
            } else {
                ++$sfwTrue;
            }

            if (null !== $maker->getAges()) {
                ++$age;
            }
        }

        $io->writeln(sprintf('Active makers:     %3d', $count));
        $io->writeln(sprintf('AVG completeness:  %6.2f%%', $completenessSum / $count));
        $io->writeln('');
        $io->writeln(sprintf('Ages:              %3d (%5.2f%%)', $age, 100 * $age / $count));
        $io->writeln(sprintf('Works w/minors:    %3d (%5.2f%%)', $countWwms, 100 * $countWwms / $count));
        $io->writeln('');
        $io->writeln(sprintf('NSFW web/soc:      %3d (%5.2f%%)', $sfwFalse, 100 * $sfwFalse / $count));
        $io->writeln(sprintf('SFW:               %3d (%5.2f%%)', $sfwTrue, 100 * $sfwTrue / $count));
        $io->writeln(sprintf('SFW unknown:       %3d (%5.2f%%)', $sfwNull, 100 * $sfwNull / $count));
    }
}
