<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Entity\Post;
use App\Entity\Submission;
use App\Utils\Enforce;

trait PathsTrait
{
    protected function getReviewPath(Submission|int|null $submissionId): string
    {
        $submissionId = Enforce::int($submissionId instanceof Submission ? $submissionId->getId() : $submissionId);

        return "/submission/$submissionId/review";
    }

    protected function getVotePath(Submission|int|null $submissionId, Post|int|null $postId, bool $positive): string
    {
        $submissionId = Enforce::int($submissionId instanceof Submission ? $submissionId->getId() : $submissionId);
        $postId = Enforce::int($postId instanceof Post ? $postId->getId() : $postId);
        $positive = (int) $positive;

        return "/submission/$submissionId/vote-post/$postId/$positive";
    }

    protected function getManagePath(Submission|int|null $submissionId): string
    {
        $submissionId = Enforce::int($submissionId instanceof Submission ? $submissionId->getId() : $submissionId);

        return "/submission/$submissionId/manage";
    }
}
