<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Post;
use App\Entity\TopicRead;
use App\Entity\User;
use App\Utils\DateTime\UtcClock;
use Veelkoov\Debris\Base\DIntMap;

class PostReadTracker
{
    /**
     * @var DIntMap<TopicRead>
     */
    private readonly DIntMap $topicsReads;

    /**
     * @param TopicRead[] $topicsReads
     */
    public function __construct(
        array $topicsReads,
        private readonly User $user,
    ) {
        $this->topicsReads = DIntMap::fromValues($topicsReads, static fn (TopicRead $topicRead) => (int) $topicRead->getId());
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
        return $this->topicsReads->getOrSet((int) $topic->getId(), fn () => new TopicRead($this->user, $topic));
    }
}
