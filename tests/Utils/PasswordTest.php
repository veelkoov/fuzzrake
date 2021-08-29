<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Password;
use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
    public function testPlaintextGetsEncrypted(): void
    {
        $artisan = new Artisan();
        $artisan->setPassword('test-password-555');

        Password::encryptOn($artisan);

        self::assertStringStartsWith('$2y$12$', $artisan->getPassword()); // We'll know when PHP changes the default algo :P
    }
}
