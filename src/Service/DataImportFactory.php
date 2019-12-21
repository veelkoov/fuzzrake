<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\Data\FdvFactory;
use App\Utils\Data\Printer;
use App\Utils\Import\DataImport;
use App\Utils\Import\Manager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataImportFactory
{
    /**
     * @var ObjectManager
     */
    private $objectManager;
    /**
     * @var FdvFactory
     */
    private $fdvFactory;

    public function __construct(ObjectManager $objectManager, FdvFactory $fdvFactory)
    {
        $this->objectManager = $objectManager;
        $this->fdvFactory = $fdvFactory;
    }

    public function get(Manager $importManager, SymfonyStyle $io): DataImport
    {
        $printer = new Printer($io);

        return new DataImport($this->objectManager, $importManager, $printer, $this->fdvFactory->create($printer));
    }
}
