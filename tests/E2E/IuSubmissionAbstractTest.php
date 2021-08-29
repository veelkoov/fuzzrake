<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Tasks\DataImport;
use App\Tests\TestUtils\DbEnabledWebTestCase;
use App\Utils\Data\FdvFactory;
use App\Utils\Data\Manager;
use App\Utils\Data\Printer;
use App\Utils\DataInputException;
use App\Utils\IuSubmissions\Finder;
use JsonException;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

abstract class IuSubmissionAbstractTest extends DbEnabledWebTestCase
{
    protected const IMPORT_DATA_DIR = __DIR__.'/../../var/testIuFormData'; // TODO: This path should be coming from the container

    protected function setUp(): void
    {
        parent::tearDown();
        $this->emptyTestSubmissionsDir();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->emptyTestSubmissionsDir();
    }

    /**
     * @throws DataInputException|JsonException
     */
    protected function getImportManager(bool $acceptAll): Manager
    {
        $corrections = '';

        if ($acceptAll) {
            foreach (Finder::getFrom(self::IMPORT_DATA_DIR) as $submission) {
                $corrections .= "with {$submission->getId()}: accept\n";
            }
        }

        return new Manager($corrections);
    }

    /**
     * @throws DataInputException|JsonException
     */
    protected function performImport(bool $acceptAll): BufferedOutput
    {
        $output = new BufferedOutput();

        $printer = new Printer(new SymfonyStyle(new StringInput(''), $output));
        $import = new DataImport(self::getEM(), $this->getImportManager($acceptAll), $printer,
            static::getContainer()->get(FdvFactory::class)->create($printer), false);

        $import->import(Finder::getFrom(self::IMPORT_DATA_DIR));

        return $output;
    }

    private function emptyTestSubmissionsDir(): void
    {
        (new Filesystem())->remove(self::IMPORT_DATA_DIR);
    }
}
