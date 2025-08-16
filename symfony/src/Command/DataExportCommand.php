<?php

declare(strict_types=1);

namespace App\Command;

use App\Data\Definitions\Fields\Fields;
use App\Entity\Creator as CreatorE;
use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Json;
use App\Utils\StrUtils;
use Generator;
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
        private readonly CreatorRepository $creatorRepository,
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
        $generator = $onlyActive
            ? $this->creatorRepository->getActivePaged()
            : $this->creatorRepository->getAllPaged();

        match (strtolower($format)) {
            'xlsx' => $this->exportToXlsx($onlyPublic, $generator),
            'json' => $this->exportToJson($onlyPublic, $generator),
            default => throw new InvalidArgumentException("Unknown format: '$format'."),
        };

        $io->success('Finished');

        return Command::SUCCESS;
    }

    /**
     * @param Generator<CreatorE> $generator
     */
    private function exportToXlsx(bool $onlyPublic, Generator $generator): void
    {
        $fieldsList = $onlyPublic ? Fields::public() : Fields::all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $col = 1;
        foreach ($fieldsList as $field) {
            $sheet->getCell([$col++, 1])->setValue($field->value);
        }

        $row = 2;
        foreach ($generator as $creatorE) {
            $creator = Creator::wrap($creatorE);

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
     * @param Generator<CreatorE> $generator
     *
     * @throws JsonException
     */
    private function exportToJson(bool $onlyPublic, Generator $generator): void
    {
        $json = '[';

        $empty = true;
        foreach ($generator as $creatorE) {
            if ($empty) {
                $empty = false;
            } else {
                $json .= ',';
            }

            $creator = Creator::wrap($creatorE);

            $json .= Json::encode($onlyPublic ? $creator->getPublicData() : $creator->getAllData());
        }

        $json .= ']';

        new Filesystem()->dumpFile('getfursu.it_data.json', $json);
    }
}
