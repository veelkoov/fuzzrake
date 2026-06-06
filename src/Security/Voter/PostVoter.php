<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Post;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<string, Post>
 */
final class PostVoter extends Voter
{
    public const string VOTE = 'vote';
    public const string EDIT = 'edit';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return arr_contains([self::VOTE, self::EDIT], $attribute) && $subject instanceof Post;
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Post $post */
        $post = $subject;

        if (!$this->accessDecisionManager->decide($token, ['review'], $post->getSubmission())) {
            return false; // You can't vote on posts if review is not accessible
        }

        return match ($attribute) {
            self::VOTE => $post->getUser() !== $user,
            self::EDIT => $post->getUser() === $user,
            default => false,
        };
    }
}
