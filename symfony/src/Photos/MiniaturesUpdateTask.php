<?php

declare(strict_types=1);

namespace App\Photos;

use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\ValueObject\Messages\UpdateMiniaturesV1;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class MiniaturesUpdateTask
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly CreatorRepository $creatorRepository,
        private readonly PhotoMiniatureResolver $resolver,
    ) {
    }

    #[AsMessageHandler]
    public function execute(UpdateMiniaturesV1 $message): void
    {
        $this->logger->info('Started miniatures update task.', ['message' => $message]);

        if (null !== $message->creatorId) {
            $this->executeForSingleCreator($message->creatorId);
        } else {
            $this->executeForAllCreators();
        }

        $this->entityManager->flush();
        $this->logger->info('Finished miniatures update task.');
    }

    private function updateCreatorMiniaturesFor(Creator $creator): void
    {
        if (0 === count($creator->getPhotoUrls())) {
            $this->logger->info("Removing miniatures of {$creator->getLastCreatorId()}.");

            $creator->setMiniatureUrls([]);

            return;
        }

        $this->logger->info("Updating miniatures for {$creator->getLastCreatorId()}...");

        $newMiniatureUrls = [];

        foreach ($creator->getPhotoUrlObjects() as $photoUrl) {
            $newMiniatureUrls[] = $this->resolver->getMiniatureUrl($photoUrl);
        }

        $creator->setMiniatureUrls($newMiniatureUrls);

        $this->logger->info("Successfully updated miniatures for {$creator->getLastCreatorId()}.");
    }

    private function executeForSingleCreator(?int $creatorId): void
    {
        $creator = $this->creatorRepository->find($creatorId);

        if (null !== $creator) {
            $this->updateCreatorMiniaturesFor(Creator::wrap($creator));
        } else {
            $this->logger->info("Creator with ID $creatorId not found. Discarding message.");
        }
    }

    private function executeForAllCreators(): void
    {
        foreach ($this->creatorRepository->getAllPaged() as $creator) {
            $creator = Creator::wrap($creator);

            if (count($creator->getMiniatureUrls()) !== count($creator->getPhotoUrls())) {
                $this->updateCreatorMiniaturesFor($creator);
            }
        }
    }
}
