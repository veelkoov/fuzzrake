<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ArtisanCommissionsStatus;
use App\Repository\ArtisanRepository;
use App\Utils\Artisan\Fields;
use App\Utils\Tracking\Status;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataExportCommand extends Command
{
    protected static $defaultName = 'app:data:export';

    public function __construct(
        private ArtisanRepository $artisans,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Export data to XLSX')
        ;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $fields = array_values(Fields::public());

        foreach ($fields as $idx => $field) {
            $sheet->getCellByColumnAndRow($idx + 1, 1)
                ->setValue($field->name());
        }

        $row = 2;

        foreach ($this->artisans->getActive() as $artisan) {
            foreach ($fields as $idx => $field) {
                $value = $artisan->get($field);

                if ($value instanceof ArtisanCommissionsStatus) {
                    $value = Status::text($value->getStatus());
                }

                $sheet->getCellByColumnAndRow($idx + 1, $row)
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
