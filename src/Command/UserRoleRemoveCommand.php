<?php

declare(strict_types=1);

namespace App\Command;

use App\Security\Role;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand('app:user:role:remove')]
class UserRoleRemoveCommand extends UserCommand
{
    public function __invoke(#[Argument] string $email, #[Argument] Role $role): int
    {
        $user = $this->findUserByEmail($email);

        $user->removeRole($role);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
