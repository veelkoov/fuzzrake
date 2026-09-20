<?php

declare(strict_types=1);

namespace App\Command;

use App\Data\Definitions\Fields\Field;
use App\Data\LabelType;
use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:one-time-apply-labels', hidden: true)]
class OneTimeApplyLabels
{
    private const array GOT_3_REVIEWS_CREATORS = [
        'PFC2021',
        'FERSUIT',
    ];

    public function __construct(
        private readonly CreatorRepository $repository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(OutputInterface $output): int
    {
        $progress = new ProgressBar($output, max: $this->repository->count());
        $progress->start();

        $counter = 0;

        foreach ($this->repository->getAllPaged() as $creatorE) {
            $this->applyLabels(new Creator($creatorE));

            if (10 === ++$counter) {
                $this->entityManager->flush();
                $counter = 0;
            }

            $progress->advance();
        }

        $this->entityManager->flush();
        $progress->finish();

        return Command::SUCCESS;
    }

    private function applyLabels(Creator $creator): void
    {
        if (array_any($creator->getAllCreatorIds(), static fn (string $creatorId) => arr_contains(self::GOT_3_REVIEWS_CREATORS, $creatorId))) {
            $creator->removeLabel(LabelType::CREATOR_ADDED_BEFORE_2026, '');
            $creator->setLabel(LabelType::CREATOR_GOT_3_REVIEWS, '', true);
        } else {
            $creator->removeLabel(LabelType::CREATOR_GOT_3_REVIEWS, '');
            $creator->setLabel(LabelType::CREATOR_ADDED_BEFORE_2026, '', true);
        }

        $this->applyLabelsToItems($creator, Field::PRODUCTS, LabelType::PRODUCT_VERIFIED_BEFORE_2026);
        $this->applyLabelsToItems($creator, Field::OFFERS, LabelType::OFFER_VERIFIED_BEFORE_2026);

        $this->entityManager->persist($creator);
    }

    private function applyLabelsToItems(Creator $creator, Field $field, LabelType $labelType): void
    {
        foreach ($creator->getStringList($field) as $item) {
            $creator->setLabel($labelType, $item, true);
        }
    }
}
