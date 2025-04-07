<?php

declare(strict_types=1);

namespace App\Species;

use App\Entity\Specie;
use App\Repository\SpecieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Veelkoov\Debris\Base\DStringMap;
use Veelkoov\Debris\Exception\MissingKeyException;
use Veelkoov\Debris\StringSet;

final class DbSpeciesService
{
    /**
     * Assuming that this service is the only instance adding/removing species during a single request.
     *
     * @var DStringMap<Specie>|null
     */
    private ?DStringMap $nameToSpecieE = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SpecieRepository $speciesRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return DStringMap<Specie>
     */
    private function getNameToSpecieE(): DStringMap
    {
        return $this->nameToSpecieE ??= DStringMap::fromValues(
            $this->speciesRepository->findAll(),
            static fn (Specie $specie) => $specie->getName(),
        );
    }

    public function assureSpeciesWithGivenNamesExist(StringSet $specieNames): void
    {
        $missingSpecieNames = $specieNames->minusAll($this->getNameToSpecieE()->getKeys());
        $this->logger->info('Creating missing species in the DB.', ['missingSpecieNames' => $missingSpecieNames]);

        foreach ($missingSpecieNames as $specieName) {
            $this->logger->info("Creating '$specieName' specie...");

            $specieEntity = (new Specie())->setName($specieName);
            $this->entityManager->persist($specieEntity);

            $this->getNameToSpecieE()->set($specieName, $specieEntity);
        }
    }

    public function removeSpeciesExceptForGivenNames(StringSet $specieNames): void
    {
        $obsoleteSpecieNames = $this->getNameToSpecieE()->getKeys()->minusAll($specieNames);
        $this->logger->info('Removing obsolete species from the DB.', ['missingSpecieNames' => $obsoleteSpecieNames]);

        foreach ($obsoleteSpecieNames as $obsoleteSpecieName) {
            $this->logger->info("Removing '$obsoleteSpecieName' specie...");

            $this->entityManager->remove($this->getNameToSpecieE()->get($obsoleteSpecieName));
        }
    }

    public function getSpecieByName(string $specieName): Specie
    {
        try {
            return $this->getNameToSpecieE()->get($specieName);
        } catch (MissingKeyException) {
            throw new SpecieException("Specie '$specieName' does not exist in the database.");
        }
    }
}
