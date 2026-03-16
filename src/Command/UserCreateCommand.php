<?php

declare(strict_types=1);

namespace App\Command;

use App\Security\UsersService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:user:create')]
class UserCreateCommand
{
    public function __construct(
        private readonly UsersService $usersService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(
        OutputInterface $output,
        #[Argument] string $email,
        #[Option] bool $verified = false,
        #[Option] bool $admin = false,
    ): int {
        $password = $this->usersService->createUser($email, $verified, $admin);
        $this->entityManager->flush();

        $output->writeln("Generated user password is: $password");

        return Command::SUCCESS;
    }
}
