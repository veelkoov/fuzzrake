<?php

declare(strict_types=1);

namespace App\Command;

use App\Data\Definitions\Fields\Field;
use App\Entity\CreatorId;
use App\Entity\Submission;
use App\Repository\CreatorIdRepository;
use App\Repository\CreatorRepository;
use App\Repository\SubmissionRepository;
use App\Utils\Creator\SmartAccessDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:set-is-update',
    description: 'Fixes "is update" on all submissions.',
)]
final class SetIsUpdateCommand
{
    public function __construct(
        private readonly SubmissionRepository $submissionRepository,
        private readonly CreatorIdRepository $creatorIdRepository,
        private readonly CreatorRepository $creatorRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
    ): int {
        foreach ($this->submissionRepository->findAll() as $submission) {
            $this->processOne($io, $submission);
        }

        $this->entityManager->flush();
        $io->success('Finished');

        return Command::SUCCESS;
    }

    private function processOne(SymfonyStyle $io, Submission $submission): void
    {
        $reader = $submission->getReader();

        $creatorId = $reader->get(Field::MAKER_ID);
        $formerIds = $reader->get(Field::FORMER_MAKER_IDS);
        $allIds = array_filter([$creatorId, ...$formerIds]);

        $count = $this->creatorIdRepository->createQueryBuilder('ci_d')
            ->select('COUNT(ci_d)')
            ->where('ci_d.creatorId IN (:ids)')
            ->setParameter('ids', $allIds)
            ->getQuery()
            ->getSingleScalarResult();

        if (0 === $count) {
            $io->text("{$submission->getStrId()} has no existing IDs - is addition.");
            $submission->setIsUpdate(false);

            return;
        }

        $query = $this->creatorRepository->createQueryBuilder('c_d')
            ->join(CreatorId::class, 'ci_d', Join::ON, 'ci_d.creator = c_d')
            ->where('ci_d.creatorId IN (:creator_id)')
            ->setParameter('creator_id', $allIds)
            ->getQuery();

        $creator = new SmartAccessDecorator($query->getSingleResult());

        if (null === $creator->getDateAdded()) {
            $io->text("{$submission->getStrId()} creator has no date added - is update.");
            $submission->setIsUpdate(true);

            return;
        }

        $dateAdded = $reader->get(Field::DATE_ADDED);
        $dateUpdated = $reader->get(Field::DATE_UPDATED);
        if (!arr_contains([null, '', 'unknown'], $dateAdded) || !arr_contains([null, '', 'unknown'], $dateUpdated)) {
            $io->text("{$submission->getStrId()} has date added/updated ($dateAdded/$dateUpdated) - is update.");
            $submission->setIsUpdate(true);

            return;
        }

        $dateTimeAdded = $creator->getDateAdded()->format(DATE_ATOM);
        $dateTimeSubmitted = $submission->getSubmittedAtUtc()->format(DATE_ATOM);

        if ($dateTimeAdded > $dateTimeSubmitted) {
            $io->text("{$submission->getStrId()} added $dateTimeAdded after submitted $dateTimeSubmitted - is addition.");
            $submission->setIsUpdate(false);

            return;
        }

        $io->text("{$submission->getStrId()} submitted $dateTimeSubmitted after added $dateTimeAdded - is update.");
        $submission->setIsUpdate(false);
    }
}
