<?php

declare(strict_types=1);

namespace App\Command;

use App\Data\Definitions\Fields\Fields;
use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\StrUtils;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:data:export',
    description: 'Export data to XLSX',
)]
final class DataExportCommand
{
    public function __construct(
        private readonly CreatorRepository $creatorRepository,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
    ): int {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $col = 1;
        foreach (Fields::public() as $field) {
            $sheet->getCell([$col++, 1])
                ->setValue($field->value);
        }

        $row = 2;

        foreach ($this->creatorRepository->getActivePaged() as $creatorE) {
            $creator = Creator::wrap($creatorE);
            $col = 1;

            foreach (Fields::public() as $field) {
                $value = $creator->get($field);

                $sheet->getCell([$col++, $row])
                    ->setValue(StrUtils::asStr($value));
            }

            ++$row;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('getfursu.it_data.xlsx');

        $io->success('Finished');

        return Command::SUCCESS;
    }
}
