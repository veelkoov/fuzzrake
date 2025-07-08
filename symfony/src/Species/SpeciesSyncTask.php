<?php

declare(strict_types=1);

namespace App\Species;

use App\Entity\Creator;
use App\Entity\CreatorSpecie;
use App\Repository\CreatorRepository;
use App\Species\Hierarchy\Species;
use App\Utils\Collections\StringList;
use App\ValueObject\Messages\SpeciesSyncNotificationV1;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Veelkoov\Debris\StringSet;

final class SpeciesSyncTask
{
    private readonly Species $species;
    private readonly CreatorSpeciesResolver $resolver;

    public function __construct(
        SpeciesService $speciesService,
        private readonly CreatorRepository $creatorRepository,
        private readonly DbSpeciesService $dbSpecies,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->species = $speciesService->species;
        $this->resolver = new CreatorSpeciesResolver($speciesService->species);
    }

    #[AsMessageHandler]
    public function execute(SpeciesSyncNotificationV1 $_): void
    {
        $this->logger->info('Started species synchronization task.');
        $this->dbSpecies->assureSpeciesWithGivenNamesExist($this->species->getVisibleNames());

        $this->logger->info('Synchronizing creators\' species...');
        foreach ($this->creatorRepository->getAllPaged() as $creator) {
            $this->syncCreatorFilterSpecies($creator);
        }
        $this->logger->info('Finished synchronizing creators\' species.');

        $this->dbSpecies->removeSpeciesExceptForGivenNames($this->species->getVisibleNames());

        $this->entityManager->flush();
        $this->logger->info('Finished species synchronization task.');
    }

    private function syncCreatorFilterSpecies(Creator $creator): void
    {
        $desiredSpecieNames = $this->resolveSpecies($creator);

        $missingSpecieNames = $desiredSpecieNames->minusAll(
            $creator->getSpecies()->map(static fn (CreatorSpecie $specie) => $specie->getSpecie()->getName()));

        foreach ($missingSpecieNames as $specieName) {
            $this->logger->info("Adding '$specieName' to $creator.");

            $creator->addSpecie(new CreatorSpecie()->setSpecie($this->dbSpecies->getSpecieByName($specieName)));
        }

        $obsoleteCreatorSpecies = $creator->getSpecies()->filter(
            static fn (CreatorSpecie $specie) => !$desiredSpecieNames->contains($specie->getSpecie()->getName()));

        foreach ($obsoleteCreatorSpecies as $specie) {
            $this->logger->info("Removing '{$specie->getSpecie()->getName()}' from $creator.");

            $creator->removeSpecie($specie);
        }
    }

    public function resolveSpecies(Creator $creator): StringSet
    {
        $doneSpecies = $this->resolver->resolveDoes(
            StringList::unpack($creator->getSpeciesDoes()),
            StringList::unpack($creator->getSpeciesDoesnt()),
        );

        return $this->resolver->resolveForFilters($doneSpecies);
    }
}
