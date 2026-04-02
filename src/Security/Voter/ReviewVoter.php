<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Data\Submission\Status;
use App\Entity\Submission;
use App\Security\Role;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<string, Submission>
 */
final class ReviewVoter extends Voter
{
    public const string SUBMISSION_REVIEW = 'submission_review';

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return arr_contains([self::SUBMISSION_REVIEW], $attribute)
            && $subject instanceof Submission;
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if (arr_contains($user->getRoles(), Role::ADMIN->value)) {
            return true;
        }

        /** @var Submission $subject */
        $submission = $subject;

        if (Status::IN_REVIEW === $submission->getStatus()) {
            return true;
        }

        return false;
    }
}
