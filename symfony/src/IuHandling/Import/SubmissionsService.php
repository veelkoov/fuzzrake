<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Entity\Submission;
use App\IuHandling\Exception\MissingSubmissionException;
use App\IuHandling\Exception\SubmissionException;
use App\Repository\SubmissionRepository;
use Doctrine\ORM\NonUniqueResultException;

class SubmissionsService
{
    public function __construct(
        private readonly SubmissionRepository $repository,
    ) {
    }

    public function getSubmissionById(string $id): Submission
    {
        try {
            return $this->repository->findByStrId($id) ?? throw new MissingSubmissionException(); // FIXME: Don't
        } catch (NonUniqueResultException $exception) {
            throw new SubmissionException(previous: $exception);
        }
    }

    public function updateEntity(Update $update): void
    {
        $this->repository->add($update->submission, true); // FIXME
    }
}
