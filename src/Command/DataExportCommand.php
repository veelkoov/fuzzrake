<?php

declare(strict_types=1);

namespace App\Command;

use App\DataDefinitions\Fields\Fields;
use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:data:export', 'Export data to XLSX')]
class DataExportCommand extends Command
{
    public function __construct(
        private readonly ArtisanRepository $artisans,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $col = 1;
        foreach (Fields::public() as $field) {
            $sheet->getCellByColumnAndRow($col++, 1)
                ->setValue($field->name);
        }

        $row = 2;

        foreach (Artisan::wrapAll($this->artisans->getActive()) as $artisan) {
            $col = 1;

            foreach (Fields::public() as $field) {
                $value = $artisan->get($field);

                $sheet->getCellByColumnAndRow($col++, $row)
                    ->setValue($value);
            }

            ++$row;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('getfursu.it_data.xlsx');

        $io->success('Finished');

        return Command::SUCCESS;
    }
}
