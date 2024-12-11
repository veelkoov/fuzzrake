<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Entity\Submission;
use App\IuHandling\Exception\MissingSubmissionException;
use App\IuHandling\Exception\SubmissionException;
use App\IuHandling\Storage\Finder;
use App\Repository\SubmissionRepository;
use App\Utils\Pagination\ItemsPage;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SubmissionsService
{
    public function __construct(
        private readonly SubmissionRepository $repository,
        #[Autowire('%env(resolve:SUBMISSIONS_DIR_PATH)%')]
        private readonly string $submissionsDirPath,
    ) {
    }

    /**
     * @param positive-int $page
     *
     * @return ItemsPage<SubmissionData>
     */
    public function getSubmissions(int $page): ItemsPage
    {
        return Finder::getFrom($this->submissionsDirPath, $page);
    }

    /**
     * @throws MissingSubmissionException
     */
    public function getUpdateInputBySubmissionId(string $id): UpdateInput
    {
        return new UpdateInput($this->getSubmissionDataById($id), $this->getSubmissionById($id));
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

    private function getSubmissionById(string $id): Submission
    {
        try {
            return $this->repository->findByStrId($id) ?? (new Submission())->setStrId($id);
        } catch (NonUniqueResultException $exception) {
            throw new SubmissionException(previous: $exception);
        }
    }

    public function updateEntity(Update $update): void
    {
        $this->repository->add($update->submission, true);
    }
}
