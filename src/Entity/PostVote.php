<?php

namespace App\Entity;

use App\Repository\PostVoteRepository;
use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostVoteRepository::class)]
#[ORM\UniqueConstraint(fields: ['post', 'user'])]
#[ORM\Index(fields: ['sentUtc'])]
#[ORM\Table(name: 'posts_votes')]
class PostVote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false)]
    private Post $post;

    #[ORM\Column]
    private bool $isPositive;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $sentUtc;

    public function __construct(User $user, Post $post, bool $isPositive)
    {
        $this->user = $user;
        $this->post = $post;
        $this->isPositive = $isPositive;
        $this->sentUtc = UtcClock::now();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function isPositive(): bool
    {
        return $this->isPositive;
    }
}
