<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\SubmissionsMigration\Finder;
use App\Command\SubmissionsMigration\SubmissionData;
use App\Entity\Submission;
use App\Repository\SubmissionRepository;
use App\Utils\DateTime\UtcClock;
use Doctrine\ORM\EntityManagerInterface;
use Psl\File;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:migrate-submissions',
)]
class MigrateSubmissionsCommand extends Command // TODO: Remove this https://github.com/veelkoov/fuzzrake/issues/290
{
    public function __construct(
        private readonly SubmissionRepository $submissionRepository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%env(resolve:SUBMISSIONS_DIR_PATH)%')]
        private readonly string $submissionsDirPath,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach (Finder::getFrom($this->submissionsDirPath) as $submissionId) {
            $submission = $this->submissionRepository->findByStrId($submissionId);

            if (null === $submission) {
                $io->info("{$submissionId} is missing in the DB, will get created.");
                $this->entityManager->persist((new Submission())->setStrId($submissionId));
            }
        }

        $this->entityManager->flush();
        $datetime2000ts = UtcClock::at('2000-01-01 00:00:00')->getTimestamp();

        foreach ($this->submissionRepository->findAll() as $submission) {
            $path = $this->submissionsDirPath.'/'.SubmissionData::getFilePathFromId($submission->getStrId());

            if ('' === $submission->getPayload()) {
                $io->info("{$submission->getStrId()} is missing payload in the DB, will get loaded.");
                $submission->setPayload(File\read($path));
            }

            if ($submission->getSubmittedAtUtc()->getTimestamp() < $datetime2000ts) {
                $io->info("{$submission->getStrId()} is missing timestamp in the DB, will get added.");
                $submission->setSubmittedAtUtc(SubmissionData::getTimestampFromFilePath($path));
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
