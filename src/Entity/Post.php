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
#[ORM\Table(name: 'posts')]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $postedUtc;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Submission::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Submission $submission;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'responses')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Post $parent;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'parent')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $responses;

    #[Assert\NotBlank]
    #[Assert\Length(max: 4096)]
    #[ORM\Column(type: Types::TEXT)]
    private string $message = '';

    public function __construct(User $user, Submission $submission, ?Post $parent = null)
    {
        $this->user = $user;
        $this->submission = $submission;
        $this->parent = $parent;
        $this->postedUtc = UtcClock::now();
        $this->responses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostedUtc(): DateTimeImmutable
    {
        return $this->postedUtc;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSubmission(): Submission
    {
        return $this->submission;
    }

    public function getParent(): ?Post
    {
        return $this->parent;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getResponses(): Collection
    {
        return $this->responses;
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
