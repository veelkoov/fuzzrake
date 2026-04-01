<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:user:show')]
class UserShowCommand extends UserCommand
{
    public function __invoke(OutputInterface $output, #[Argument] string $email): int
    {
        $user = $this->findUserByEmail($email);

        $roles = implode(', ', $user->getRoles());
        $roles = '' === $roles ? '(none assigned)' : $roles;

        $output->writeln("ID: {$user->getId()}");
        $output->writeln("Email: {$user->getEmail()}");
        $output->writeln("Roles: {$roles}");

        return Command::SUCCESS;
    }
}
