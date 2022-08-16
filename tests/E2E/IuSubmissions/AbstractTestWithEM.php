<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Tasks\DataImport;
use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use App\Tests\TestUtils\Paths;
use App\Utils\Data\FdvFactory;
use App\Utils\Data\Manager;
use App\Utils\Data\Printer;
use App\Utils\IuSubmissions\Finder;
use Exception;
use RuntimeException;
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

    protected function performImport(bool $acceptAll): BufferedOutput
    {
        $output = new BufferedOutput();

        $printer = new Printer(new SymfonyStyle(new StringInput(''), $output));
        $importManager = $this->getImportManager($acceptAll);
        $fdv = $this->getFdvFactory()->create($printer);
        $import = new DataImport(self::getEM(), $importManager, $printer, $fdv);

        $import->import(Finder::getFrom(Paths::getTestIuFormDataPath()));

        return $output;
    }

    private function emptyTestSubmissionsDir(): void
    {
        (new Filesystem())->remove(Paths::getTestIuFormDataPath());
    }

    private function getFdvFactory(): FdvFactory
    {
        try {
            $result = self::getContainer()->get(FdvFactory::class);
        } catch (Exception $cause) {
            throw new RuntimeException(previous: $cause);
        }

        self::assertInstanceOf(FdvFactory::class, $result);

        return $result;
    }
}
