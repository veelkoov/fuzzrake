<?php

declare(strict_types=1);

namespace App\Photos;

use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\ValueObject\Messages\UpdateMiniaturesV1;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class UpdateMiniaturesMessageHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly CreatorRepository $creatorRepository,
        private readonly MiniaturesUpdater $updater,
    ) {
    }

    #[AsMessageHandler]
    public function handle(UpdateMiniaturesV1 $message): void
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

    private function executeForSingleCreator(?int $creatorId): void
    {
        $creator = $this->creatorRepository->find($creatorId);

        if (null !== $creator) {
            $this->updater->updateCreatorMiniaturesFor(Creator::wrap($creator), true);
        } else {
            $this->logger->info("Creator with ID $creatorId not found. Discarding message.");
        }
    }

    private function executeForAllCreators(): void
    {
        foreach ($this->creatorRepository->getAllPaged() as $creator) {
            $this->updater->updateCreatorMiniaturesFor(Creator::wrap($creator), false);
        }
    }
}
