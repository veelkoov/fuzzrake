<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Utils\UnbelievableRuntimeException;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersService
{
    private const string RANDOM_PASSWORD_CHARACTERS = '-_0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const int RANDOM_PASSWORD_LENGTH = 32;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function createUser(string $email, bool $isVerified = false, bool $isAdmin = false): string
    {
        $user = new User()->setEmail($email);

        if ($isVerified) {
            $user->addRole(Role::VERIFIED);
        }
        if ($isAdmin) {
            $user->addRole(Role::ADMIN);
        }

        $plainPassword = $this->getRandomPassword();
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);

        return $plainPassword;
    }

    // Based on https://stackoverflow.com/a/31284266/583786
    private function getRandomPassword(): string
    {
        try {
            $result = '';

            $charactersMax = strlen(self::RANDOM_PASSWORD_CHARACTERS) - 1;
            for ($i = 0; $i < self::RANDOM_PASSWORD_LENGTH; ++$i) {
                $result .= self::RANDOM_PASSWORD_CHARACTERS[random_int(0, $charactersMax)];
            }

            return $result;
        } catch (RandomException $exception) {
            throw new UnbelievableRuntimeException($exception); // What is wrong with your OS, bro
        }
    }
}
