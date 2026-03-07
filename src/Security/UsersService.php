<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use RuntimeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersService
{
    public const string RANDOM_PASSWORD_CHARACTERS = '-_0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    public const int RANDOM_PASSWORD_LENGHT = 32;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function createUser(string $email, bool $isVerified = false, bool $isAdmin = false): string
    {
        $password = $this->getRandomPassword();

        $user = new User()
            ->setEmail($email)
            ->setIsVerified($isVerified)
        ;

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        if ($isAdmin) {
            $user->setRoles(array_values($user->getRoles() + ['ROLE_ADMIN']));
        }

        $this->entityManager->persist($user);

        return $password;
    }

    private function getRandomPassword(): string
    {
        try {
            $result = '';

            $charactersMax = strlen(self::RANDOM_PASSWORD_CHARACTERS) - 1;
            for ($i = 0; $i < self::RANDOM_PASSWORD_LENGHT; ++$i) {
                $result .= self::RANDOM_PASSWORD_CHARACTERS[random_int(0, $charactersMax)];
            }

            return $result;
        } catch (RandomException $exception) {
            throw new RuntimeException(previous: $exception);
        }
    }
}
