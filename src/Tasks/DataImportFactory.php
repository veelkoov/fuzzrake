<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Utils\Data\FdvFactory;
use App\Utils\Data\Printer;
use App\Utils\DataInput\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataImportFactory
{
    private EntityManagerInterface $objectManager;
    private FdvFactory $fdvFactory;

    public function __construct(EntityManagerInterface $objectManager, FdvFactory $fdvFactory)
    {
        $this->objectManager = $objectManager;
        $this->fdvFactory = $fdvFactory;
    }

    public function get(Manager $importManager, SymfonyStyle $io, $showAllFixCmds): DataImport
    {
        $printer = new Printer($io);

        return new DataImport($this->objectManager, $importManager, $printer, $this->fdvFactory->create($printer),
            $showAllFixCmds);
    }
}
