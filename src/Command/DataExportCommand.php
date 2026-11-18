<?php

declare(strict_types=1);

namespace App\Command;

use App\Data\Definitions\Fields\Fields;
use App\Service\DataService;
use App\Utils\StrUtils;
use InvalidArgumentException;
use JsonException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:data:export',
    description: 'Export data to a file',
)]
final class DataExportCommand
{
    public function __construct(
        private readonly DataService $dataService,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function __invoke(
        SymfonyStyle $io,
        #[Option]
        bool $includeInactive = false,
        #[Option(suggestedValues: ['xlsx', 'json'])]
        string $format = 'xlsx',
    ): int {
        match (strtolower($format)) {
            'xlsx' => $this->exportToXlsx($includeInactive),
            'json' => $this->exportToJson($includeInactive),
            default => throw new InvalidArgumentException("Unknown format: '$format'."),
        };

        $io->success('Finished');

        return Command::SUCCESS;
    }

    private function exportToXlsx(bool $includeInactive): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $col = 1;
        foreach (Fields::public() as $field) {
            $sheet->getCell([$col++, 1])->setValue($field->value);
        }

        $row = 2;
        foreach ($this->dataService->getCreatorsData($includeInactive) as $creator) {
            $col = 1;
            foreach (Fields::public() as $field) {
                $sheet->getCell([$col++, $row])->setValue(StrUtils::asStr($creator->get($field)));
            }

            ++$row;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('getfursu.it_data.xlsx');
    }

    /**
     * @throws JsonException
     */
    private function exportToJson(bool $includeInactive): void
    {
        $json = $this->dataService->getCreatorsPublicDataJsonString($includeInactive);

        new Filesystem()->dumpFile('getfursu.it_data.json', $json);
    }
}
