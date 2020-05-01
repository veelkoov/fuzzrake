<?php

declare(strict_types=1);

namespace App\Command;

use App\Utils\Data\Definitions\Species;
use App\Utils\Species\Specie;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateSpeciesDotCommand extends Command
{
    protected static $defaultName = 'app:data:generate-species-dot';

    private Species $species;

    public function __construct(Species $species)
    {
        parent::__construct();

        $this->species = $species;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('digraph SPECIES {');

        $hidden = '[style = invis]';

        $species = $this->species->getSpeciesFlat();
        $splitGroups = ['Mammals', 'Fantasy creatures', 'Ungulates', 'Copyright-related classification?'];

        foreach ($splitGroups as $specieName) {
            $children = $species[$specieName]->getChildren();
            usort($children, function (Specie $a, Specie $b): int {
                return count($a->getDescendants()) - count($b->getDescendants());
            });

            $childCount = count($children);
            $colNum = ceil(sqrt($childCount));

            for ($ci = 0; $ci < $childCount;) {
                $io->write('{ rank = same; ');

                for ($ir = 0; $ir < $colNum && $ci < $childCount; $ir++, $ci++) {
                    $io->write('"'.$children[$ci]->getName().'"; ');
                }

                $io->writeln('}');
            }

            for ($ci = $colNum; $ci < $childCount; ++$ci) {
                $io->writeln('"'.$children[$ci - $colNum]->getName().'" -> "'.$children[$ci]->getName().'" '.$hidden);
            }
        }

        foreach ($species as $specie) {
            if ('to_be_tidied' === $specie->getName() || $specie->isDescendantOf($species['to_be_tidied']) || (!$specie->isRoot() && $specie->isLeaf())) {
                continue;
            }

            if ($specie->isRoot()) {
                $io->writeln("\"{$specie->getName()}\" [penwidth=5]");
            }

            $io->write("\"{$specie->getName()}\"");

            $children = implode('", "', $specie->getChildren());
            if ('' !== $children) {
                $io->write(" -> { \"$children\" }");
            }

            $io->writeln('');
        }

        $io->writeln([
            '"Deers" -> "Fantasy creatures" '.$hidden,

            '"Robotic/cybernetic/mechanical" -> H01 -> "Others" -> H02 -> "Any/most species" -> H03 -> "Copyright-related classification?" '.$hidden,
            '{ rank = same; "Robotic/cybernetic/mechanical"; "Others"; "Any/most species"; "Copyright-related classification?"; H01; H02; H03 }',

            'H01 '.$hidden,
            'H02 '.$hidden,
            'H03 '.$hidden,
        ]);

        $io->writeln('}');

        return 0;
    }
}
