<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

abstract class UserCommand
{
    public function __construct(
        protected readonly UserRepository $userRepository,
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    protected function findUserByEmail(string $email): User
    {
        return $this->userRepository->findOneBy(['email' => $email])
            ?? throw new InvalidArgumentException('User with the given email address does not exist.');
    }
}
