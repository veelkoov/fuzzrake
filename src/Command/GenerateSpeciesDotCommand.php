<?php

declare(strict_types=1);

namespace App\Command;

use App\Data\Species\Specie;
use App\Data\Species\SpeciesService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsCommand('app:data:generate-species-dot')]
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

    private readonly Filesystem $fs;

    public function __construct(
        private readonly SpeciesService $speciesSrv,
    ) {
        parent::__construct();

        $this->fs = new Filesystem();
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

        return Command::SUCCESS;
    }

    private function getDotFileContents(): string
    {
        $res = "graph SPECIES {\n";

        $hidden = '[style = invis]';

        $species = $this->speciesSrv->getSpecies()->list;

        foreach (self::GROUPS_WITH_ARTIFICIAL_PLACEMENT as $specieName) {
            $children = $this->visible($species->getByName($specieName)->getChildren());
            usort($children, fn (Specie $a, Specie $b): int => count($a->getDescendants()) - count($b->getDescendants()));

            $childCount = count($children);
            $colNum = ceil(sqrt($childCount));

            for ($ci = 0; $ci < $childCount;) {
                $res .= '{ rank = same; ';

                for ($ir = 0; $ir < $colNum && $ci < $childCount; $ir++, $ci++) {
                    $res .= '"'.$children[$ci]->name.'"; ';
                }

                $res .= "}\n";
            }

            for ($ci = $colNum; $ci < $childCount; ++$ci) {
                $res .= '"'.$children[$ci - $colNum]->name.'" -- "'.$children[$ci]->name.'" '.$hidden."\n";
            }
        }

        foreach ($species->items as $specie) {
            if ($specie->hidden || (!$specie->isRoot() && $specie->isLeaf())) {
                continue;
            }

            if (in_array($specie->name, self::BOLD_GROUPS, true)) {
                $res .= "\"{$specie->name}\" [penwidth=5]\n";
            }

            $res .= "\"{$specie->name}\"";

            $children = implode('", "', $this->visible($specie->getChildren()));
            if ('' !== $children) {
                $res .= " -- { \"$children\" }";
            }

            $res .= "\n";
        }

        $res .= '}';

        return $res;
    }

    /**
     * @param Specie[] $species
     *
     * @return Specie[]
     */
    private function visible(array $species): array
    {
        return array_filter($species, fn (Specie $specie) => !$specie->hidden);
    }
}
