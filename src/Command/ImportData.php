<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DataImporter;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use ZipArchive;

class ImportData extends Command
{
    protected static $defaultName = 'app:data:import';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var DataImporter
     */
    private $dataImporter;

    public function __construct(DataImporter $dataImporter)
    {
        $this->dataImporter = $dataImporter;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('dry-run', 'd', null, 'Dry run (don\'t update the DB)');
        $this->addArgument('input-file', InputArgument::REQUIRED, 'Input file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $filePath = $input->getArgument('input-file');

        if (!file_exists($filePath)) {
            $this->io->error("File '$filePath' does not exist");
            return 1;
        }

        $this->dataImporter->import($this->arrayFromCsvFile($filePath));

        if (!$input->getOption('dry-run')) {
//            $this->objectManager->flush(); // FIXME
            $this->io->success('Finished and saved');
        } else {
            $this->io->success('Finished without saving');
        }
    }

    private function arrayFromCsvFile(string $filePath): array
    {
        if ('application/zip' == MimeTypeGuesser::getInstance()->guess($filePath)) {
            $fileContents = $this->readOnlyFileInZip($filePath);
        } else {
            $fileContents = file_get_contents($filePath);
        }

        return $this->arrayFromCsvString($fileContents);
    }

    private function arrayFromCsvString(string $csvString): array
    {
        $fileHandle = $this->getTmpFileWithContents($csvString);

        $result = $this->arrayFromCsvFileHandle($fileHandle);
        array_shift($result); // Drop header

        fclose($fileHandle);

        return $result;
    }

    private function arrayFromCsvFileHandle($fileHandle): array
    {
        $result = [];
        while (false !== ($cols = fgetcsv($fileHandle, 0, ',', '"', '"'))) {
            $result[] = $cols;
        }
        return $result;
    }

    private function readOnlyFileInZip(string $filePath): string
    {
        $zip = new ZipArchive();
        if (true !== $zip->open($filePath)) {
            throw new InvalidArgumentException("File '$filePath' is a corrupted ZIP archive");
        }

        if ($zip->numFiles !== 1) {
            $zip->close();
            throw new InvalidArgumentException("Unexpected number of files in the '$filePath' ZIP archive: {$zip->numFiles}");
        }

        $result = $zip->getFromIndex(0);
        $zip->close();

        if ($result === false) {
            throw new InvalidArgumentException("Failed reading file from the '$filePath' ZIP archive");
        }

        return $result;
    }

    private function getTmpFileWithContents(string $contents)
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $contents);
        rewind($handle);

        return $handle;
    }
}
