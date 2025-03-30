<?php

declare(strict_types=1);

namespace App\Species;

use App\Entity\Artisan as Creator;
use App\Entity\CreatorSpecie;
use App\Entity\Specie as SpecieE;
use App\Repository\ArtisanRepository as CreatorRepository;
use App\Repository\SpecieRepository;
use App\Utils\Collections\StringList;
use App\ValueObject\Messages\SpeciesSyncNotificationV1;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Veelkoov\Debris\Base\DMap;
use Veelkoov\Debris\Exception\MissingKeyException;
use Veelkoov\Debris\StringSet;

final class SpeciesSyncTask // TODO: Tests
{
    private readonly Species $species;
    private readonly CreatorSpeciesResolver $resolver;

    public function __construct(
        SpeciesService $speciesService,
        private readonly SpecieRepository $speciesRepository,
        private readonly CreatorRepository $creatorRepository,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->species = $speciesService->species;
        $this->resolver = new CreatorSpeciesResolver($speciesService->species);
    }

    #[AsMessageHandler]
    public function execute(SpeciesSyncNotificationV1 $_): void
    {
        $dbSpecies = $this->getSpeciesFromDatabase();

        $this->createMissingSpeciesAddingToMap($dbSpecies);

        foreach ($this->creatorRepository->getAllPaged() as $creator) {
            $this->syncCreatorSpecies($creator, $dbSpecies);
        }

        $this->removeObsoleteSpecies($dbSpecies);

        $this->entityManager->flush();
    }

    private function syncCreatorSpecies(Creator $creator, StringSpecieMap $dbSpecies): void
    {
        $desiredSpecieNames = $this->getResolvedDoes($creator);

        $missingSpecieNames = $desiredSpecieNames->minusAll(
            $creator->getSpecies()->map(static fn (CreatorSpecie $specie) => $specie->getSpecie()->getName()));

        foreach ($missingSpecieNames as $specieName) {
            $this->logger->info("Adding '$specieName' to $creator...");

            try {
                $specie = $dbSpecies->get($specieName);
            } catch (MissingKeyException) {
                throw new SpecieException("$creator resolved specie '$specieName' does not exist in the database");
            }

            $creator->addSpecie((new CreatorSpecie())->setSpecie($specie));
        }

        $obsoleteSpecies = DMap::fromValues($creator->getSpecies(),
            static fn (CreatorSpecie $specie) => $specie->getSpecie()->getName())
            ->filterKeys(static fn (string $specieName) => !$desiredSpecieNames->contains($specieName));

        foreach ($obsoleteSpecies as $specie) {
            $this->logger->info("Removing '{$specie->getSpecie()->getName()}' from $creator...");

            $creator->removeSpecie($specie);
        }
    }

    private function createMissingSpeciesAddingToMap(StringSpecieMap $dbSpecies): void
    {
        $missingNames = $this->species->getVisibleNames()->minusAll($dbSpecies->getNames());

        foreach ($missingNames as $specieName) {
            $this->logger->info("Creating '$specieName' specie...");

            $specieEntity = (new SpecieE())->setName($specieName);
            $this->entityManager->persist($specieEntity);

            $dbSpecies->set($specieName, $specieEntity);
        }
    }

    private function removeObsoleteSpecies(StringSpecieMap $dbSpecies): void
    {
        foreach ($dbSpecies->getNames()->minusAll($this->species->getVisibleNames()) as $unnededSpecieName) {
            $this->logger->info("Removing '$unnededSpecieName' specie...");

            $this->entityManager->remove($this->species->getByName($unnededSpecieName));
        }
    }

    private function getSpeciesFromDatabase(): StringSpecieMap
    {
        return StringSpecieMap::fromValues(
            $this->speciesRepository->findAll(),
            static fn (SpecieE $specie) => $specie->getName(),
        );
    }

    private function getResolvedDoes(Creator $creator): StringSet
    {
        return $this->resolver->resolveDoes(
            StringList::unpack($creator->getSpeciesDoes()),
            StringList::unpack($creator->getSpeciesDoesnt()),
        );
    }
}
