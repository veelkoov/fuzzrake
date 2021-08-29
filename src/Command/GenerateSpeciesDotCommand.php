<?php

declare(strict_types=1);

namespace App\Command;

use App\Utils\Species\Specie;
use App\Utils\Species\Species;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GenerateSpeciesDotCommand extends Command
{
    private const DOT_FILE_PATH = 'species.dot';

    private const GROUPS_WITH_ARTIFICIAL_PLACEMENT = [
        'Mammals',
        'Fantasy creatures',
        'Ungulates',
    ];

    private const BOLD_GROUPS = [
        'Most species',
        'With beak',
        'Winged',
        'Hooved',
        'Aquatic',
        'With antlers',
        'Fantasy creatures',
        'Real life animals',
    ];

    private Filesystem $fs;

    public function __construct(
        private Species $species,
    ) {
        parent::__construct();

        $this->fs = new Filesystem();
    }

    protected function configure()
    {
        $this
            ->setName('app:data:generate-species-dot')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fs->dumpFile(self::DOT_FILE_PATH, $this->getDotFileContents());

        $process = new Process(['dot', '-O', '-Tpng', self::DOT_FILE_PATH]);
        $process->run();

        $this->fs->remove(self::DOT_FILE_PATH);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return 0;
    }

    private function getDotFileContents(): string
    {
        $res = "graph SPECIES {\n";

        $hidden = '[style = invis]';

        $species = $this->species->getFlat();
        foreach (self::GROUPS_WITH_ARTIFICIAL_PLACEMENT as $specieName) {
            $children = $species[$specieName]->getChildren();
            usort($children, fn (Specie $a, Specie $b): int => count($a->getDescendants()) - count($b->getDescendants()));

            $childCount = count($children);
            $colNum = ceil(sqrt($childCount));

            for ($ci = 0; $ci < $childCount;) {
                $res .= '{ rank = same; ';

                for ($ir = 0; $ir < $colNum && $ci < $childCount; $ir++, $ci++) {
                    $res .= '"'.$children[$ci]->getName().'"; ';
                }

                $res .= "}\n";
            }

            for ($ci = $colNum; $ci < $childCount; ++$ci) {
                $res .= '"'.$children[$ci - $colNum]->getName().'" -- "'.$children[$ci]->getName().'" '.$hidden."\n";
            }
        }

        foreach ($species as $specie) {
            if (!$specie->isRoot() && $specie->isLeaf()) {
                continue;
            }

            if (in_array($specie->getName(), self::BOLD_GROUPS)) {
                $res .= "\"{$specie->getName()}\" [penwidth=5]\n";
            }

            $res .= "\"{$specie->getName()}\"";

            $children = implode('", "', $specie->getChildren());
            if ('' !== $children) {
                $res .= " -- { \"$children\" }";
            }

            $res .= "\n";
        }

        $res .= '}';

        return $res;
    }
}
