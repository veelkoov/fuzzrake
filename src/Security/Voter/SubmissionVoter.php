<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Data\Submission\Status;
use App\Entity\Submission;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<string, Submission>
 */
final class SubmissionVoter extends Voter
{
    public const string REVIEW = 'review';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return arr_contains([self::REVIEW], $attribute) && $subject instanceof Submission;
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if (self::REVIEW !== $attribute) {
            return false;
        }

        /** @var Submission $subject */
        $submission = $subject;

        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true; // Admin can always access reviews
        }

        // Reviewers cannot access review if submission is not in the right status
        return Status::IN_REVIEW === $submission->getStatus();
    }
}
