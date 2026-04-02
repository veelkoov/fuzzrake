<?php

declare(strict_types=1);

namespace App\Entity;

use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'discussion_comments')]
class DiscussionComment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $postedUtc;

    #[ORM\ManyToOne(targetEntity: DiscussionTopic::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private DiscussionTopic $topic;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[Assert\NotBlank]
    #[Assert\Length(max: 4096)]
    #[ORM\Column(type: Types::TEXT)]
    private string $message = '';

    public function __construct(DiscussionTopic $topic, User $user)
    {
        $this->topic = $topic;
        $this->user = $user;
        $this->postedUtc = UtcClock::now();
    }

    public function getPostedUtc(): DateTimeImmutable
    {
        return $this->postedUtc;
    }

    public function getTopic(): DiscussionTopic
    {
        return $this->topic;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
