<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Tasks\DataImport;
use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use App\Tests\TestUtils\Paths;
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

abstract class AbstractTestWithEM extends WebTestCaseWithEM
{
    protected function setUp(): void
    {
        parent::setUp();
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
            foreach (Finder::getFrom(Paths::getTestIuFormDataPath()) as $submission) {
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

        $import->import(Finder::getFrom(Paths::getTestIuFormDataPath()));

        return $output;
    }

    private function emptyTestSubmissionsDir(): void
    {
        (new Filesystem())->remove(Paths::getTestIuFormDataPath());
    }
}
