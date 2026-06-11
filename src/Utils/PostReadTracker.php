<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Post;
use App\Entity\TopicRead;
use App\Entity\User;
use App\Utils\DateTime\UtcClock;

class PostReadTracker
{
    /**
     * @param TopicRead[] $topicsReads
     */
    public function __construct(
        private array $topicsReads,
        private readonly User $user,
    ) {
    }

    public function isRead(Post $post): bool
    {
        $topic = null === $post->getParent() ? $post : $post->getParent();
        $topicRead = $this->getTopicReadFor($topic);

        return $topicRead->getLastRead()->getTimestamp() > ($post->getEditedUtc()?->getTimestamp() ?? $post->getPostedUtc()->getTimestamp());
    }

    /**
     * @param positive-int $timestampUtc
     */
    public function markRead(Post $topic, int $timestampUtc): TopicRead
    {
        return $this->getTopicReadFor($topic)->setLastRead(UtcClock::fromTimestamp($timestampUtc));
    }

    private function getTopicReadFor(Post $topic): TopicRead
    {
        foreach ($this->topicsReads as $topicRead) {
            if ($topicRead->getTopic() === $topic) {
                return $topicRead;
            }
        }

        $result = new TopicRead($this->user, $topic);
        $this->topicsReads[] = $result;

        return $result;
    }
}
