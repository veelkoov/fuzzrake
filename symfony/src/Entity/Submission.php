<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SubmissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubmissionRepository::class)]
#[ORM\Table(name: 'submissions')]
class Submission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, unique: true)]
    private string $strId = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $directives = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $comment = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStrId(): string
    {
        return $this->strId;
    }

    public function setStrId(string $strId): Submission
    {
        $this->strId = $strId;

        return $this;
    }

    public function getDirectives(): string
    {
        return $this->directives;
    }

    public function setDirectives(string $directives): Submission
    {
        $this->directives = $directives;

        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): Submission
    {
        $this->comment = $comment;

        return $this;
    }
}
