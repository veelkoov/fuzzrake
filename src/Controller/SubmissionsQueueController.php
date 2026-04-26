<?php

declare(strict_types=1);

namespace App\Controller;

use App\Data\Submission\Status;
use App\Repository\SubmissionRepository;
use App\Repository\UserRoleRepository;
use App\Security\Role;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SubmissionsQueueController extends AbstractController
{
    private const array IGNORED_SUBMISSIONS_STATUSES = [Status::IMPORTED, Status::REJECTED, Status::REPLACED];

    public function __construct(
        private readonly SubmissionRepository $submissionRepository,
        private readonly UserRoleRepository $roleRepository,
    ) {
    }

    #[Route('queue', name: 'rt_submission_queue_main')]
    public function main(
    ): Response {
        return $this->render('submissions_queue/main.html.twig', [
            'statistics' => $this->submissionRepository->getStatusToCount()->minusAllKeys(self::IGNORED_SUBMISSIONS_STATUSES),
            'reviewers_count' => $this->countReviewers(),
        ]);
    }

    private function countReviewers(): int
    {
        // This ignores roles hierarchy, which as of 2026-04-26 translates to 0 reviewers

        return $this->roleRepository->count(['role' => Role::REVIEWER])
            - 1 // the maintainer has a dedicated separate reviewer account
        ;
    }
}
