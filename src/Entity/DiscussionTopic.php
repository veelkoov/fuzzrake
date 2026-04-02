<?php

declare(strict_types=1);

namespace App\Entity;

use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'discussion_topics')]
class DiscussionTopic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $postedUtc;

    #[ORM\ManyToOne(targetEntity: Submission::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Submission $submission;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    /**
     * @var Collection<int, DiscussionComment>
     */
    #[ORM\OneToMany(targetEntity: DiscussionComment::class, mappedBy: 'topic')]
    private Collection $comments;

    #[Assert\NotBlank]
    #[Assert\Length(max: 4096)]
    #[ORM\Column(type: Types::TEXT)]
    private string $message = '';

    public function __construct(Submission $submission, User $user)
    {
        $this->submission = $submission;
        $this->user = $user;
        $this->postedUtc = UtcClock::now();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostedUtc(): DateTimeImmutable
    {
        return $this->postedUtc;
    }

    public function getSubmission(): Submission
    {
        return $this->submission;
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

    /**
     * @return Collection<int, DiscussionComment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }
}
