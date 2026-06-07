<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TopicReadRepository;
use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'topics_reads')]
#[ORM\UniqueConstraint(fields: ['user', 'topic'])]
#[ORM\Entity(repositoryClass: TopicReadRepository::class)]
class TopicRead
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Post $topic;

    #[ORM\Column]
    private DateTimeImmutable $lastRead;

    public function __construct(User $user, Post $topic)
    {
        $this->user = $user;
        $this->topic = $topic;

        $this->lastRead = UtcClock::now();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTopic(): Post
    {
        return $this->topic;
    }

    public function getLastRead(): DateTimeImmutable
    {
        return $this->lastRead;
    }

    public function setLastRead(DateTimeImmutable $lastRead): static
    {
        $this->lastRead = $lastRead;

        return $this;
    }
}
