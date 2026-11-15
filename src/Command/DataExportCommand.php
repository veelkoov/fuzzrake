<?php

declare(strict_types=1);

namespace App\Command;

use App\Data\Definitions\Fields\Fields;
use App\Repository\CreatorRepository;
use App\Service\DataService;
use App\Utils\Json;
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
use Veelkoov\Debris\Vecs\IntVec;

#[AsCommand(
    name: 'app:data:export',
    description: 'Export data to a file',
)]
final class DataExportCommand
{
    public function __construct(
        private readonly CreatorRepository $creatorRepository,
        private readonly DataService $dataService,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function __invoke(
        SymfonyStyle $io,
        #[Option]
        bool $onlyPublic = true,
        #[Option]
        bool $onlyActive = true,
        #[Option(suggestedValues: ['xlsx', 'json'])]
        string $format = 'xlsx',
    ): int {
        $ids = $onlyActive
            ? $this->creatorRepository->getActiveIds()
            : $this->creatorRepository->getAllIds();

        match (strtolower($format)) {
            'xlsx' => $this->exportToXlsx($onlyPublic, $ids),
            'json' => $this->exportToJson($onlyPublic, $ids),
            default => throw new InvalidArgumentException("Unknown format: '$format'."),
        };

        $io->success('Finished');

        return Command::SUCCESS;
    }

    private function exportToXlsx(bool $onlyPublic, IntVec $ids): void
    {
        $fieldsList = $onlyPublic ? Fields::public() : Fields::all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $col = 1;
        foreach ($fieldsList as $field) {
            $sheet->getCell([$col++, 1])->setValue($field->value);
        }

        $row = 2;
        foreach ($ids as $id) {
            $creator = $this->dataService->getCreatorDataById($id);

            $col = 1;
            foreach ($fieldsList as $field) {
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
    private function exportToJson(bool $onlyPublic, IntVec $ids): void
    {
        $json = '[';

        $empty = true;
        foreach ($ids as $id) {
            if ($empty) {
                $empty = false;
            } else {
                $json .= ',';
            }

            $creator = $this->dataService->getCreatorDataById($id);

            $json .= Json::encode($onlyPublic ? $creator->getPublicData() : $creator->getAllData());
        }

        $json .= ']';

        new Filesystem()->dumpFile('getfursu.it_data.json', $json);
    }
}
