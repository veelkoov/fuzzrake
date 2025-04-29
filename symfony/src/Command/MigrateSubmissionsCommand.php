<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\SubmissionsMigration\Finder;
use App\Command\SubmissionsMigration\SubmissionData;
use App\Entity\Submission;
use App\IuHandling\Exception\MissingSubmissionException;
use App\Repository\SubmissionRepository;
use App\Utils\Pagination\ItemsPage;
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
        private readonly SubmissionRepository $submissionRepository, // @phpstan-ignore property.onlyWritten
        private readonly EntityManagerInterface $entityManager, // @phpstan-ignore property.onlyWritten
        #[Autowire('%env(resolve:SUBMISSIONS_DIR_PATH)%')]
        private readonly string $submissionsDirPath,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        return Command::SUCCESS;
    }

    public function fillData(Submission $submission): void
    {
        $path = $this->submissionsDirPath.'/'.SubmissionData::getFilePathFromId($submission->getStrId());

        if ('' === $submission->getPayload()) {
            $submission->setPayload(File\read($path));
        }
    }

    /**
     * @param positive-int $page
     *
     * @return ItemsPage<SubmissionData>
     */
    private function getSubmissions(int $page): ItemsPage
    {
        return Finder::getFrom($this->submissionsDirPath, $page);
    }

    /**
     * @throws MissingSubmissionException
     */
    private function getSubmissionDataById(string $id): SubmissionData
    {
        $result = Finder::getSingleFrom($this->submissionsDirPath, $id);

        if (null === $result) {
            throw new MissingSubmissionException("Couldn't find the submission with the given ID: '$id'");
        }

        return $result;
    }
}
