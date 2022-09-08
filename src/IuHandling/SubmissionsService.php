<?php

declare(strict_types=1);

namespace App\IuHandling;

use App\Entity\Submission;
use App\Repository\SubmissionRepository;
use App\Utils\IuSubmissions\Finder;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use function Psl\Iter\first;
use function Psl\Vec\filter;

class SubmissionsService
{
    private readonly string $submissionsDirPath;

    public function __construct(
        private readonly SubmissionRepository $repository,
        #[Autowire('%env(resolve:SUBMISSIONS_DIR_PATH)%')]
        string $submissionsDirPath,
    ) {
        $this->submissionsDirPath = $submissionsDirPath;
    }

    /**
     * @return SubmissionData[]
     */
    public function getSubmissions(): array
    {
        return Finder::getFrom($this->submissionsDirPath, limit: 20, reverse: true);
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
        $result = first(filter($this->getSubmissions(), fn ($submission) => $submission->getId() === $id));

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
